<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BudgetAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Alex Rivera',
                'password' => Hash::make('password'),
            ]
        );

        // Accounts
        $checking = Account::firstOrCreate([
            'user_id' => $user->id,
            'name' => 'Main Checking',
        ], [
            'type' => 'bank',
            'balance' => 4850.20,
            'currency' => 'USD',
            'color' => '#6366f1',
            'icon' => 'building-library',
            'is_active' => true,
        ]);

        $savings = Account::firstOrCreate([
            'user_id' => $user->id,
            'name' => 'High-Yield Savings',
        ], [
            'type' => 'savings',
            'balance' => 12400.00,
            'currency' => 'USD',
            'color' => '#10b981',
            'icon' => 'banknotes',
            'is_active' => true,
        ]);

        $cash = Account::firstOrCreate([
            'user_id' => $user->id,
            'name' => 'Cash Wallet',
        ], [
            'type' => 'cash',
            'balance' => 240.00,
            'currency' => 'USD',
            'color' => '#f59e0b',
            'icon' => 'wallet',
            'is_active' => true,
        ]);

        // Categories
        $categoriesData = [
            ['name' => 'Groceries', 'type' => 'expense', 'icon' => 'shopping-cart', 'color' => '#10b981', 'sort_order' => 1, 'budget' => 600.00],
            ['name' => 'Dining & Drinks', 'type' => 'expense', 'icon' => 'cake', 'color' => '#f59e0b', 'sort_order' => 2, 'budget' => 300.00],
            ['name' => 'Housing & Utilities', 'type' => 'expense', 'icon' => 'home', 'color' => '#3b82f6', 'sort_order' => 3, 'budget' => 1200.00],
            ['name' => 'Transport & Gas', 'type' => 'expense', 'icon' => 'truck', 'color' => '#06b6d4', 'sort_order' => 4, 'budget' => 200.00],
            ['name' => 'Entertainment', 'type' => 'expense', 'icon' => 'film', 'color' => '#ec4899', 'sort_order' => 5, 'budget' => 250.00],
            ['name' => 'Shopping', 'type' => 'expense', 'icon' => 'shopping-bag', 'color' => '#8b5cf6', 'sort_order' => 6, 'budget' => 350.00],
            ['name' => 'Health & Fitness', 'type' => 'expense', 'icon' => 'heart', 'color' => '#ef4444', 'sort_order' => 7, 'budget' => 150.00],
            ['name' => 'Subscriptions', 'type' => 'expense', 'icon' => 'arrow-path', 'color' => '#14b8a6', 'sort_order' => 8, 'budget' => 80.00],
            ['name' => 'Salary', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#22c55e', 'sort_order' => 9, 'budget' => 0],
            ['name' => 'Freelance / Side Gig', 'type' => 'income', 'icon' => 'sparkles', 'color' => '#a855f7', 'sort_order' => 10, 'budget' => 0],
            ['name' => 'Investment Returns', 'type' => 'income', 'icon' => 'chart-bar', 'color' => '#0ea5e9', 'sort_order' => 11, 'budget' => 0],
        ];

        $currentMonth = Carbon::now()->format('Y-m');
        $createdCategories = [];

        foreach ($categoriesData as $item) {
            $cat = Category::firstOrCreate([
                'user_id' => $user->id,
                'name' => $item['name'],
            ], [
                'type' => $item['type'],
                'icon' => $item['icon'],
                'color' => $item['color'],
                'sort_order' => $item['sort_order'],
            ]);

            $createdCategories[$item['name']] = $cat;

            if ($item['budget'] > 0) {
                Budget::firstOrCreate([
                    'user_id' => $user->id,
                    'category_id' => $cat->id,
                    'period_month' => $currentMonth,
                ], [
                    'monthly_limit' => $item['budget'],
                    'alert_threshold' => 80,
                ]);
            }
        }

        // Sample Transactions for current month
        if (Transaction::where('user_id', $user->id)->count() === 0) {
            $now = Carbon::now();

            // Income
            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $checking->id,
                'category_id' => $createdCategories['Salary']->id,
                'type' => 'income',
                'amount' => 3800.00,
                'transacted_at' => $now->copy()->startOfMonth()->addDays(1)->setTime(9, 0),
                'note' => 'Monthly Salary Deposit',
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $checking->id,
                'category_id' => $createdCategories['Freelance / Side Gig']->id,
                'type' => 'income',
                'amount' => 450.00,
                'transacted_at' => $now->copy()->subDays(6)->setTime(15, 30),
                'note' => 'UI Design Consultation',
            ]);

            // Expenses
            $sampleExpenses = [
                ['cat' => 'Housing & Utilities', 'amount' => 1100.00, 'days_ago' => 15, 'note' => 'Apartment Rent'],
                ['cat' => 'Housing & Utilities', 'amount' => 85.00, 'days_ago' => 12, 'note' => 'Electric & Water bill'],
                ['cat' => 'Groceries', 'amount' => 142.50, 'days_ago' => 10, 'note' => 'Whole Foods Market'],
                ['cat' => 'Groceries', 'amount' => 89.20, 'days_ago' => 5, 'note' => 'Trader Joe\'s weekly run'],
                ['cat' => 'Groceries', 'amount' => 45.80, 'days_ago' => 1, 'note' => 'Local Organic Market'],
                ['cat' => 'Dining & Drinks', 'amount' => 64.00, 'days_ago' => 8, 'note' => 'Dinner at Italian Bistro'],
                ['cat' => 'Dining & Drinks', 'amount' => 14.50, 'days_ago' => 4, 'note' => 'Coffee & Pastry with team'],
                ['cat' => 'Dining & Drinks', 'amount' => 48.00, 'days_ago' => 2, 'note' => 'Sushi takeout'],
                ['cat' => 'Transport & Gas', 'amount' => 55.00, 'days_ago' => 11, 'note' => 'Shell Gas Station refill'],
                ['cat' => 'Transport & Gas', 'amount' => 24.50, 'days_ago' => 3, 'note' => 'Uber ride to airport'],
                ['cat' => 'Entertainment', 'amount' => 35.00, 'days_ago' => 7, 'note' => 'Cinema IMAX tickets'],
                ['cat' => 'Shopping', 'amount' => 89.99, 'days_ago' => 9, 'note' => 'Nike running shoes sale'],
                ['cat' => 'Subscriptions', 'amount' => 15.99, 'days_ago' => 14, 'note' => 'Netflix Premium 4K'],
                ['cat' => 'Subscriptions', 'amount' => 10.99, 'days_ago' => 14, 'note' => 'Spotify Family Plan'],
                ['cat' => 'Health & Fitness', 'amount' => 65.00, 'days_ago' => 13, 'note' => 'Gym Membership monthly'],
            ];

            foreach ($sampleExpenses as $expense) {
                Transaction::create([
                    'user_id' => $user->id,
                    'account_id' => $checking->id,
                    'category_id' => $createdCategories[$expense['cat']]->id,
                    'type' => 'expense',
                    'amount' => $expense['amount'],
                    'transacted_at' => $now->copy()->subDays($expense['days_ago'])->setTime(rand(9, 20), rand(10, 59)),
                    'note' => $expense['note'],
                ]);
            }
        }
    }
}
