<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    public static function currencySymbol(?string $currency = 'EUR'): string
    {
        return match ($currency) {
            'USD' => '$',
            'EUR' => '€',
            default => '€',
        };
    }

    /**
     * Get aggregated dashboard summary for a user and period.
     */
    public function getDashboardSummary(User $user, ?string $month = null, ?int $accountId = null): array
    {
        $month = $month ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth();
        $currency = $user->currency ?: 'EUR';
        $currencySymbol = self::currencySymbol($currency);

        // Total balances across active accounts
        $accounts = $user->accounts()->where('is_active', true)->get();
        $totalBalance = $accounts->sum('balance');

        // Month transactions
        $transactionsQuery = $user->transactions()
            ->whereBetween('transacted_at', [$startOfMonth, $endOfMonth]);

        $monthlyIncome = (float) (clone $transactionsQuery)
            ->where('type', 'income')
            ->sum('amount');

        $monthlyExpense = (float) (clone $transactionsQuery)
            ->where('type', 'expense')
            ->sum('amount');

        // Savings rate = (Income - Expense) / Income * 100
        $savingsRate = $monthlyIncome > 0
            ? max(0, round((($monthlyIncome - $monthlyExpense) / $monthlyIncome) * 100))
            : 0;

        // Total Monthly Budget Limit
        $budgets = $user->budgets()
            ->with('category')
            ->where('period_month', $month)
            ->get();

        $totalBudgetLimit = (float) $budgets->sum('monthly_limit');

        // Category spending this month
        $categorySpending = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transacted_at', [$startOfMonth, $endOfMonth])
            ->select('category_id', DB::raw('SUM(amount) as total_spent'))
            ->groupBy('category_id')
            ->pluck('total_spent', 'category_id')
            ->toArray();

        // Enrich categories with spent amount and budget
        $categories = $user->categories()
            ->where('type', 'expense')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) use ($categorySpending, $budgets) {
                $spent = (float) ($categorySpending[$cat->id] ?? 0);
                $budget = $budgets->firstWhere('category_id', $cat->id);
                $limit = $budget ? (float) $budget->monthly_limit : 0;
                $percentage = $limit > 0 ? min(100, round(($spent / $limit) * 100)) : 0;
                $isOver = $limit > 0 && $spent > $limit;

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'icon' => $cat->icon,
                    'color' => $cat->color,
                    'spent' => $spent,
                    'limit' => $limit,
                    'percentage' => $percentage,
                    'is_over' => $isOver,
                ];
            });

        // Recent 10 transactions
        $recentTransactions = $user->transactions()
            ->with(['category', 'account'])
            ->orderBy('transacted_at', 'desc')
            ->limit(10)
            ->get();

        // Daily Spending Limit & Savings Target Calculations
        $savingsTargetPercentage = (float) ($user->savings_target_percentage ?? 20.0);
        $expectedIncome = $user->expected_monthly_income ? (float) $user->expected_monthly_income : null;
        $effectiveIncome = $monthlyIncome > 0 ? $monthlyIncome : ($expectedIncome ?: 2000.0);
        $targetSavingsAmount = round($effectiveIncome * ($savingsTargetPercentage / 100), 2);
        $monthlySpendableBudget = max(0, $effectiveIncome - $targetSavingsAmount);
        
        $daysInMonth = $startOfMonth->daysInMonth;
        $isCurrentMonth = Carbon::now()->format('Y-m') === $month;
        $todayDay = $isCurrentMonth ? (int) Carbon::now()->format('j') : 1;
        $daysRemaining = $isCurrentMonth ? max(1, $daysInMonth - $todayDay + 1) : $daysInMonth;

        // Daily limit = (Income - Target Savings) / Total calendar days in month
        $dailySpendingLimit = $daysInMonth > 0 ? round($monthlySpendableBudget / $daysInMonth, 2) : 0;
        $dailyBaseLimit = $dailySpendingLimit;

        $spentToday = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereDate('transacted_at', Carbon::today())
            ->sum('amount');

        $todayRemainingDaily = round($dailySpendingLimit - $spentToday, 2);
        $todayIsOver = $spentToday > $dailySpendingLimit;
        $todayPercentage = $dailySpendingLimit > 0 ? min(100, round(($spentToday / $dailySpendingLimit) * 100)) : 0;

        // Effective monthly budget for spending after target savings
        $effectiveBudgetLimit = $monthlySpendableBudget > 0 ? $monthlySpendableBudget : ($totalBudgetLimit > 0 ? $totalBudgetLimit : $effectiveIncome);
        $budgetPercentage = $effectiveBudgetLimit > 0 ? min(100, round(($monthlyExpense / $effectiveBudgetLimit) * 100)) : 0;
        $remainingBudget = max(0, $effectiveBudgetLimit - $monthlyExpense);

        return [
            'month' => $month,
            'formatted_month' => $startOfMonth->format('F Y'),
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'total_balance' => $totalBalance,
            'monthly_income' => $monthlyIncome,
            'monthly_expense' => $monthlyExpense,
            'savings_rate' => $savingsTargetPercentage,
            'savings_target_percentage' => $savingsTargetPercentage,
            'expected_monthly_income' => $expectedIncome,
            'effective_income' => $effectiveIncome,
            'target_savings_amount' => $targetSavingsAmount,
            'monthly_spendable_budget' => $monthlySpendableBudget,
            'days_in_month' => $daysInMonth,
            'days_remaining' => $daysRemaining,
            'today_day' => $todayDay,
            'spent_today' => $spentToday,
            'daily_spending_limit' => $dailySpendingLimit,
            'daily_base_limit' => $dailyBaseLimit,
            'today_remaining_daily' => $todayRemainingDaily,
            'today_is_over' => $todayIsOver,
            'today_percentage' => $todayPercentage,
            'total_budget_limit' => $effectiveBudgetLimit,
            'category_budget_limit_sum' => $totalBudgetLimit,
            'total_budget_spent' => $monthlyExpense,
            'budget_percentage' => $budgetPercentage,
            'remaining_budget' => $remainingBudget,
            'categories' => $categories,
            'accounts' => $accounts,
            'recent_transactions' => $recentTransactions,
        ];
    }

    /**
     * Get detailed budgets & analytics breakdown.
     */
    public function getBudgetsAnalytics(User $user, ?string $month = null): array
    {
        $currency = $user->currency ?: 'EUR';
        $currencySymbol = self::currencySymbol($currency);
        $month = $month ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth();

        $budgets = $user->budgets()
            ->with('category')
            ->where('period_month', $month)
            ->get();

        $totalLimit = (float) $budgets->sum('monthly_limit');

        $categorySpending = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transacted_at', [$startOfMonth, $endOfMonth])
            ->select('category_id', DB::raw('SUM(amount) as total_spent'))
            ->groupBy('category_id')
            ->pluck('total_spent', 'category_id')
            ->toArray();

        $totalSpent = array_sum($categorySpending);

        $categoryBudgets = $user->categories()
            ->where('type', 'expense')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) use ($categorySpending, $budgets, $totalSpent) {
                $spent = (float) ($categorySpending[$cat->id] ?? 0);
                $budget = $budgets->firstWhere('category_id', $cat->id);
                $limit = $budget ? (float) $budget->monthly_limit : 0;
                $pctOfBudget = $limit > 0 ? round(($spent / $limit) * 100) : 0;
                $pctOfTotalSpend = $totalSpent > 0 ? round(($spent / $totalSpent) * 100, 1) : 0;
                $isOver = $limit > 0 && $spent > $limit;

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'icon' => $cat->icon,
                    'color' => $cat->color,
                    'spent' => $spent,
                    'limit' => $limit,
                    'pct_of_budget' => $pctOfBudget,
                    'pct_of_total_spend' => $pctOfTotalSpend,
                    'is_over' => $isOver,
                    'remaining' => max(0, $limit - $spent),
                ];
            });

        // Daily Spending Limit & Savings Target Calculations
        $savingsTargetPercentage = (float) ($user->savings_target_percentage ?? 20.0);
        $expectedIncome = $user->expected_monthly_income ? (float) $user->expected_monthly_income : null;
        $monthlyIncome = (float) $user->transactions()
            ->whereBetween('transacted_at', [$startOfMonth, $endOfMonth])
            ->where('type', 'income')
            ->sum('amount');
        $effectiveIncome = $monthlyIncome > 0 ? $monthlyIncome : ($expectedIncome ?: 2000.0);
        $targetSavingsAmount = round($effectiveIncome * ($savingsTargetPercentage / 100), 2);
        $monthlySpendableBudget = max(0, $effectiveIncome - $targetSavingsAmount);

        $daysInMonth = $startOfMonth->daysInMonth;
        $isCurrentMonth = Carbon::now()->format('Y-m') === $month;
        $todayDay = $isCurrentMonth ? (int) Carbon::now()->format('j') : 1;
        $daysRemaining = $isCurrentMonth ? max(1, $daysInMonth - $todayDay + 1) : $daysInMonth;

        // Daily limit = (Income - Target Savings) / Total calendar days in month
        $dailySpendingLimit = $daysInMonth > 0 ? round($monthlySpendableBudget / $daysInMonth, 2) : 0;
        $dailyBaseLimit = $dailySpendingLimit;

        $spentToday = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereDate('transacted_at', Carbon::today())
            ->sum('amount');

        $todayRemainingDaily = round($dailySpendingLimit - $spentToday, 2);
        $todayIsOver = $spentToday > $dailySpendingLimit;
        $todayPercentage = $dailySpendingLimit > 0 ? min(100, round(($spentToday / $dailySpendingLimit) * 100)) : 0;

        $effectiveTotalLimit = $monthlySpendableBudget > 0 ? $monthlySpendableBudget : ($totalLimit > 0 ? $totalLimit : $effectiveIncome);

        return [
            'month' => $month,
            'formatted_month' => $startOfMonth->format('F Y'),
            'prev_month' => $startOfMonth->copy()->subMonth()->format('Y-m'),
            'next_month' => $startOfMonth->copy()->addMonth()->format('Y-m'),
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'total_limit' => $effectiveTotalLimit,
            'category_limit_sum' => $totalLimit,
            'total_spent' => $totalSpent,
            'overall_percentage' => $effectiveTotalLimit > 0 ? round(($totalSpent / $effectiveTotalLimit) * 100) : 0,
            'remaining_budget' => max(0, $effectiveTotalLimit - $totalSpent),
            'monthly_income' => $monthlyIncome,
            'savings_target_percentage' => $savingsTargetPercentage,
            'expected_monthly_income' => $expectedIncome,
            'effective_income' => $effectiveIncome,
            'target_savings_amount' => $targetSavingsAmount,
            'monthly_spendable_budget' => $monthlySpendableBudget,
            'days_in_month' => $daysInMonth,
            'days_remaining' => $daysRemaining,
            'today_day' => $todayDay,
            'spent_today' => $spentToday,
            'daily_spending_limit' => $dailySpendingLimit,
            'daily_base_limit' => $dailyBaseLimit,
            'today_remaining_daily' => $todayRemainingDaily,
            'today_is_over' => $todayIsOver,
            'today_percentage' => $todayPercentage,
            'category_budgets' => $categoryBudgets,
        ];
    }

    /**
     * Get complete spending and income analytics data with visual chart segments.
     */
    public function getAnalyticsData(User $user, ?string $month = null, string $type = 'expense'): array
    {
        $type = in_array($type, ['expense', 'income']) ? $type : 'expense';
        $month = $month ?: Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth();
        $currency = $user->currency ?: 'EUR';
        $currencySymbol = self::currencySymbol($currency);

        // Fetch all user categories for the selected type (both seeded and manually created ones)
        $categories = $user->categories()
            ->where('type', $type)
            ->orderBy('sort_order')
            ->get();

        // Fetch user category budgets for this month
        $budgets = $user->budgets()
            ->where('period_month', $month)
            ->get();

        // Sum category spending/income for the month
        $categorySpending = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->whereBetween('transacted_at', [$startOfMonth, $endOfMonth])
            ->select('category_id', DB::raw('SUM(amount) as total_spent'))
            ->groupBy('category_id')
            ->pluck('total_spent', 'category_id')
            ->toArray();

        // Check for transactions without a category
        $uncategorizedSpent = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->whereNull('category_id')
            ->whereBetween('transacted_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalAmount = array_sum($categorySpending) + $uncategorizedSpent;

        // Map every category with actual spending/income, budget limits and percentages
        $categoryBreakdown = $categories->map(function ($cat) use ($categorySpending, $budgets, $totalAmount, $type) {
            $spent = (float) ($categorySpending[$cat->id] ?? 0);
            $budget = $budgets->firstWhere('category_id', $cat->id);
            $limit = $budget ? (float) $budget->monthly_limit : 0;
            $pctOfTotal = $totalAmount > 0 ? round(($spent / $totalAmount) * 100, 1) : 0;
            $pctOfLimit = $limit > 0 ? min(100, round(($spent / $limit) * 100)) : 0;
            $isOver = $limit > 0 && $spent > $limit;

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'icon' => $cat->icon,
                'color' => $cat->color,
                'type' => $cat->type,
                'total' => $spent,
                'limit' => $limit,
                'pct_of_total' => $pctOfTotal,
                'pct_of_limit' => $pctOfLimit,
                'is_over' => $isOver,
                'remaining' => max(0, $limit - $spent),
            ];
        })->sortByDesc('total')->values();

        if ($uncategorizedSpent > 0) {
            $pctOfTotal = $totalAmount > 0 ? round(($uncategorizedSpent / $totalAmount) * 100, 1) : 0;
            $categoryBreakdown->push([
                'id' => 0,
                'name' => __('Uncategorized'),
                'icon' => 'question-mark-circle',
                'color' => '#94a3b8',
                'type' => $type,
                'total' => $uncategorizedSpent,
                'limit' => 0,
                'pct_of_total' => $pctOfTotal,
                'pct_of_limit' => 0,
                'is_over' => false,
                'remaining' => 0,
            ]);
        }

        // Generate segmented Donut chart geometry (stroke-dasharray and stroke-dashoffset on circum=100)
        $donutSegments = [];
        $accumulatedPct = 0;
        foreach ($categoryBreakdown as $cat) {
            if ($cat['total'] > 0 && $totalAmount > 0) {
                $pct = round(($cat['total'] / $totalAmount) * 100, 2);
                $offset = $accumulatedPct;
                $accumulatedPct += $pct;
                $donutSegments[] = [
                    'id' => $cat['id'],
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'amount' => $cat['total'],
                    'percentage' => $pct,
                    'dasharray' => "{$pct} 100",
                    'dashoffset' => -$offset,
                ];
            }
        }

        // 6 months trend
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $mStart = $m->copy()->startOfMonth();
            $mEnd = $m->copy()->endOfMonth();

            $inc = (float) Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereBetween('transacted_at', [$mStart, $mEnd])
                ->sum('amount');

            $exp = (float) Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereBetween('transacted_at', [$mStart, $mEnd])
                ->sum('amount');

            $monthlyTrends[] = [
                'month' => $m->format('Y-m'),
                'label' => $m->format('M'),
                'income' => $inc,
                'expense' => $exp,
                'savings' => max(0, $inc - $exp),
            ];
        }

        return [
            'month' => $month,
            'formatted_month' => $startOfMonth->format('F Y'),
            'prev_month' => $startOfMonth->copy()->subMonth()->format('Y-m'),
            'next_month' => $startOfMonth->copy()->addMonth()->format('Y-m'),
            'currency' => $currency,
            'currency_symbol' => $currencySymbol,
            'type' => $type,
            'total_spent' => $totalAmount,
            'category_breakdown' => $categoryBreakdown,
            'donut_segments' => $donutSegments,
            'monthly_trends' => $monthlyTrends,
            'active_category_count' => count($donutSegments),
            'total_category_count' => $categoryBreakdown->count(),
        ];
    }

    /**
     * Record a new transaction and update the associated account balance.
     */
    public function recordTransaction(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data) {
            $account = Account::where('user_id', $user->id)->findOrFail($data['account_id']);
            $amount = (float) $data['amount'];
            $type = $data['type'] ?? 'expense';

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'account_id' => $account->id,
                'category_id' => $data['category_id'] ?? null,
                'type' => $type,
                'amount' => $amount,
                'transacted_at' => $data['transacted_at'] ?? Carbon::now(),
                'note' => $data['note'] ?? null,
                'destination_account_id' => $data['destination_account_id'] ?? null,
                'is_recurring' => !empty($data['is_recurring']),
            ]);

            // Adjust account balances
            if ($type === 'expense') {
                $account->decrement('balance', $amount);
            } elseif ($type === 'income') {
                $account->increment('balance', $amount);
            } elseif ($type === 'transfer' && !empty($data['destination_account_id'])) {
                $account->decrement('balance', $amount);
                $dest = Account::where('user_id', $user->id)->find($data['destination_account_id']);
                if ($dest) {
                    $dest->increment('balance', $amount);
                }
            }

            return $transaction;
        });
    }

    /**
     * Update an existing transaction and adjust account balances accordingly.
     */
    public function updateTransaction(User $user, int $transactionId, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $transactionId, $data) {
            $tx = Transaction::where('user_id', $user->id)->findOrFail($transactionId);
            $oldAccount = $tx->account;
            $oldAmount = (float) $tx->amount;
            $oldType = $tx->type;
            $oldDestId = $tx->destination_account_id;

            // 1. Revert previous transaction effects on old account(s)
            if ($oldAccount) {
                if ($oldType === 'expense') {
                    $oldAccount->increment('balance', $oldAmount);
                } elseif ($oldType === 'income') {
                    $oldAccount->decrement('balance', $oldAmount);
                } elseif ($oldType === 'transfer' && $oldDestId) {
                    $oldAccount->increment('balance', $oldAmount);
                    $oldDest = Account::where('user_id', $user->id)->find($oldDestId);
                    if ($oldDest) {
                        $oldDest->decrement('balance', $oldAmount);
                    }
                }
            }

            // 2. Fetch new account and parse new data
            $newAccount = Account::where('user_id', $user->id)->findOrFail($data['account_id']);
            $newAmount = (float) $data['amount'];
            $newType = $data['type'] ?? 'expense';
            $newDestId = ($newType === 'transfer') ? ($data['destination_account_id'] ?? null) : null;
            $newCategoryId = ($newType === 'transfer') ? null : ($data['category_id'] ?? null);

            // 3. Update the transaction record
            $tx->update([
                'account_id' => $newAccount->id,
                'category_id' => $newCategoryId,
                'type' => $newType,
                'amount' => $newAmount,
                'transacted_at' => $data['transacted_at'] ?? Carbon::now(),
                'note' => $data['note'] ?? null,
                'destination_account_id' => $newDestId,
                'is_recurring' => !empty($data['is_recurring']),
            ]);

            // 4. Apply new transaction effects on new account(s)
            if ($newType === 'expense') {
                $newAccount->decrement('balance', $newAmount);
            } elseif ($newType === 'income') {
                $newAccount->increment('balance', $newAmount);
            } elseif ($newType === 'transfer' && $newDestId) {
                $newAccount->decrement('balance', $newAmount);
                $newDest = Account::where('user_id', $user->id)->find($newDestId);
                if ($newDest) {
                    $newDest->increment('balance', $newAmount);
                }
            }

            return $tx->fresh(['category', 'account', 'destinationAccount']);
        });
    }

    /**
     * Delete a transaction and reverse balance.
     */
    public function deleteTransaction(User $user, int $transactionId): bool
    {
        return DB::transaction(function () use ($user, $transactionId) {
            $tx = Transaction::where('user_id', $user->id)->findOrFail($transactionId);
            $account = $tx->account;
            $amount = (float) $tx->amount;

            if ($account) {
                if ($tx->type === 'expense') {
                    $account->increment('balance', $amount);
                } elseif ($tx->type === 'income') {
                    $account->decrement('balance', $amount);
                } elseif ($tx->type === 'transfer' && $tx->destination_account_id) {
                    $account->increment('balance', $amount);
                    $dest = Account::where('user_id', $user->id)->find($tx->destination_account_id);
                    if ($dest) {
                        $dest->decrement('balance', $amount);
                    }
                }
            }

            return $tx->delete();
        });
    }
}
