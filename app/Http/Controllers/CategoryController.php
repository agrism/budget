<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
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
        $currentMonth = Carbon::now()->format('Y-m');

        $categories = $user->categories()
            ->with(['budgets' => function ($q) use ($currentMonth) {
                $q->where('period_month', $currentMonth);
            }])
            ->withCount('transactions')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $currency = $user->currency ?: 'EUR';
        $currencySymbol = BudgetService::currencySymbol($currency);

        if ($request->header('HX-Request')) {
            return view('categories.partials.list', compact('categories', 'currencySymbol', 'currentMonth'));
        }

        return view('categories.index', compact('categories', 'currencySymbol', 'currentMonth'));
    }

    public function show(Category $category, Request $request)
    {
        $user = $this->getActiveUser();
        if ($category->user_id && $category->user_id !== $user->id) {
            abort(403);
        }

        $transactions = Transaction::where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->with(['category', 'account'])
            ->orderBy('transacted_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        if ($request->header('HX-Request') && $request->has('feed_only')) {
            return view('categories.partials.transactions_feed', compact('category', 'transactions'));
        }

        $currentMonth = Carbon::now()->format('Y-m');
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $thisMonthSpent = (float) Transaction::where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->whereBetween('transacted_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $allTimeSpent = (float) Transaction::where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->sum('amount');

        $budget = $category->budgets()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhereNull('user_id');
            })
            ->where('period_month', $currentMonth)
            ->first();

        $monthlyLimit = $budget ? (float) $budget->monthly_limit : null;

        $currency = $user->currency ?: 'EUR';
        $currencySymbol = BudgetService::currencySymbol($currency);

        return view('categories.show', compact(
            'category',
            'transactions',
            'thisMonthSpent',
            'allTimeSpent',
            'monthlyLimit',
            'currencySymbol',
            'currentMonth'
        ));
    }

    public function transactionsFeed(Category $category, Request $request)
    {
        $user = $this->getActiveUser();
        if ($category->user_id && $category->user_id !== $user->id) {
            abort(403);
        }

        $transactions = Transaction::where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->with(['category', 'account'])
            ->orderBy('transacted_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('categories.partials.transactions_feed', compact('category', 'transactions'));
    }

    public function create(Request $request)
    {
        $defaultType = $request->get('type', 'expense');
        return view('categories.modal_create', compact('defaultType'));
    }

    public function store(Request $request)
    {
        $user = $this->getActiveUser();

        $validated = $request->validate([
            'name' => 'required|string|max:60',
            'type' => 'required|in:expense,income',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'monthly_limit' => 'nullable|numeric|min:0',
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
            'sort_order' => $user->categories()->max('sort_order') + 1,
        ]);

        if (!empty($validated['monthly_limit']) && $validated['type'] === 'expense') {
            $currentMonth = Carbon::now()->format('Y-m');
            Budget::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'period_month' => $currentMonth,
                ],
                [
                    'monthly_limit' => $validated['monthly_limit'],
                ]
            );
        }

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'categoryCreated' => true,
                    'budgetUpdated' => true,
                    'closeModal' => true,
                    'showToast' => __('Category created successfully!'),
                ]));
        }

        return redirect()->back()->with('success', __('Category created successfully!'));
    }

    public function edit(Category $category)
    {
        $user = $this->getActiveUser();
        if ($category->user_id && $category->user_id !== $user->id) {
            abort(403);
        }

        $currentMonth = Carbon::now()->format('Y-m');
        $budget = $category->budgets()->where('period_month', $currentMonth)->first();
        $monthlyLimit = $budget ? (float) $budget->monthly_limit : null;

        return view('categories.modal_edit', compact('category', 'monthlyLimit', 'currentMonth'));
    }

    public function update(Request $request, Category $category)
    {
        $user = $this->getActiveUser();
        if ($category->user_id && $category->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:60',
            'type' => 'required|in:expense,income',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'monthly_limit' => 'nullable|numeric|min:0',
        ]);

        $category->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
        ]);

        $currentMonth = Carbon::now()->format('Y-m');
        if ($validated['type'] === 'expense') {
            if ($validated['monthly_limit'] !== null && $validated['monthly_limit'] !== '') {
                Budget::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'category_id' => $category->id,
                        'period_month' => $currentMonth,
                    ],
                    [
                        'monthly_limit' => $validated['monthly_limit'],
                    ]
                );
            }
        }

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'categoryUpdated' => true,
                    'budgetUpdated' => true,
                    'closeModal' => true,
                    'showToast' => __('Category updated successfully!'),
                ]));
        }

        return redirect()->back()->with('success', __('Category updated successfully!'));
    }

    public function destroy(Request $request, Category $category)
    {
        $user = $this->getActiveUser();
        if ($category->user_id && $category->user_id !== $user->id) {
            abort(403);
        }

        // Dissociate from transactions
        $category->transactions()->update(['category_id' => null]);
        // Delete budgets
        $category->budgets()->delete();
        $category->delete();

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'categoryDeleted' => true,
                    'budgetUpdated' => true,
                    'showToast' => __('Category deleted.'),
                ]));
        }

        return redirect()->back()->with('success', __('Category deleted.'));
    }
}
