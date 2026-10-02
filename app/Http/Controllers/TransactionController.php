<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
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

    public function create(Request $request)
    {
        $user = $this->getActiveUser();
        $accounts = $user->accounts()->where('is_active', true)->get();
        $categories = $user->categories()->orderBy('sort_order')->get();
        $defaultType = $request->get('type', 'expense');

        return view('transactions.modal_create', compact('accounts', 'categories', 'defaultType'));
    }

    public function store(Request $request)
    {
        $user = $this->getActiveUser();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:expense,income,transfer',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'destination_account_id' => 'nullable|exists:accounts,id',
            'transacted_at' => 'nullable|date',
            'note' => 'nullable|string|max:255',
            'is_recurring' => 'nullable|boolean',
        ]);

        if (empty($validated['transacted_at'])) {
            $validated['transacted_at'] = Carbon::now();
        }

        $transaction = $this->budgetService->recordTransaction($user, $validated);

        if ($request->header('HX-Request')) {
            // Trigger client-side events for HTMX to update balance and recent transactions
            return response('')
                ->header('HX-Trigger', json_encode([
                    'transactionCreated' => true,
                    'closeModal' => true,
                    'showToast' => 'Transaction saved successfully!',
                ]));
        }

        return redirect()->route('dashboard')->with('success', 'Transaction saved!');
    }

    public function edit(Transaction $transaction)
    {
        $user = $this->getActiveUser();

        if ($transaction->user_id !== $user->id) {
            abort(403);
        }

        $accounts = $user->accounts()->where('is_active', true)->get();
        if (!$accounts->contains('id', $transaction->account_id)) {
            $txAcc = $transaction->account;
            if ($txAcc) {
                $accounts->push($txAcc);
            }
        }

        $categories = $user->categories()->orderBy('sort_order')->get();

        return view('transactions.modal_edit', compact('transaction', 'accounts', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $user = $this->getActiveUser();

        if ($transaction->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:expense,income,transfer',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'destination_account_id' => 'nullable|exists:accounts,id',
            'transacted_at' => 'nullable|date',
            'note' => 'nullable|string|max:255',
            'is_recurring' => 'nullable|boolean',
        ]);

        if (empty($validated['transacted_at'])) {
            $validated['transacted_at'] = Carbon::now();
        }

        $this->budgetService->updateTransaction($user, $transaction->id, $validated);

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'transactionUpdated' => true,
                    'closeModal' => true,
                    'showToast' => __('Transaction updated successfully!'),
                ]));
        }

        return redirect()->route('dashboard')->with('success', __('Transaction updated!'));
    }

    public function destroy(Transaction $transaction, Request $request)
    {
        $user = $this->getActiveUser();

        if ($transaction->user_id !== $user->id) {
            abort(403);
        }

        $this->budgetService->deleteTransaction($user, $transaction->id);

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'transactionDeleted' => true,
                    'showToast' => 'Transaction deleted.',
                ]));
        }

        return redirect()->back()->with('success', 'Transaction deleted.');
    }
}
