<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Transaction;

class BudgetController extends Controller
{
    public function __construct(protected BudgetService $budgetService) {}

    protected function getActiveUser(): User
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->getActiveUser();
        $search = $request->get('search');
        $type = $request->get('type');
        $accountId = $request->get('account_id');
        $categoryId = $request->get('category_id');

        $query = $user->transactions()
            ->with(['category', 'account', 'destinationAccount'])
            ->orderBy('transacted_at', 'desc')
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('account', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($type && in_array($type, ['expense', 'income', 'transfer'])) {
            $query->where('type', $type);
        }

        if ($accountId) {
            $query->where(function ($q) use ($accountId) {
                $q->where('account_id', $accountId)
                  ->orWhere('destination_account_id', $accountId);
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $accounts = $user->accounts()->where('is_active', true)->get();
        $categories = $user->categories()->orderBy('sort_order')->get();

        // Transaction counts for filter pills (across all accounts or filtered account/category)
        $baseCountQuery = $user->transactions();
        if ($accountId) {
            $baseCountQuery->where(function ($q) use ($accountId) {
                $q->where('account_id', $accountId)
                  ->orWhere('destination_account_id', $accountId);
            });
        }
        if ($categoryId) {
            $baseCountQuery->where('category_id', $categoryId);
        }

        $counts = [
            'all' => (clone $baseCountQuery)->count(),
            'expense' => (clone $baseCountQuery)->where('type', 'expense')->count(),
            'income' => (clone $baseCountQuery)->where('type', 'income')->count(),
            'transfer' => (clone $baseCountQuery)->where('type', 'transfer')->count(),
        ];

        // Overall income / expense sums for summary stats
        $totalExpense = (float) (clone $baseCountQuery)->where('type', 'expense')->sum('amount');
        $totalIncome = (float) (clone $baseCountQuery)->where('type', 'income')->sum('amount');
        $netSavings = $totalIncome - $totalExpense;

        $currency = $user->currency ?: 'EUR';
        $currencySymbol = BudgetService::currencySymbol($currency);

        if ($request->header('HX-Request') && $request->has('feed_only')) {
            return view('budgets.partials.transactions_feed', compact('transactions', 'currencySymbol', 'accountId', 'categoryId'));
        }

        $viewData = compact(
            'transactions',
            'accounts',
            'categories',
            'counts',
            'currencySymbol',
            'type',
            'search',
            'accountId',
            'categoryId',
            'totalExpense',
            'totalIncome',
            'netSavings'
        );

        if ($request->header('HX-Request')) {
            return view('budgets.partials.content', $viewData);
        }

        return view('budgets.index', $viewData);
    }

    public function update(Request $request)
    {
        $user = $this->getActiveUser();

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'monthly_limit' => 'required|numeric|min:0',
            'period_month' => 'required|string|size:7',
        ]);

        Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $validated['category_id'],
                'period_month' => $validated['period_month'],
            ],
            [
                'monthly_limit' => $validated['monthly_limit'],
            ]
        );

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'budgetUpdated' => true,
                    'closeModal' => true,
                    'showToast' => 'Budget limit updated!',
                ]));
        }

        return redirect()->back()->with('success', 'Budget updated.');
    }
}
