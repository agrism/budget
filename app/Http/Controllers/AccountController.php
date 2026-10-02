<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
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
        $accounts = $user->accounts()->withCount('transactions')->get();
        $totalBalance = $accounts->sum('balance');

        if ($request->header('HX-Request')) {
            return view('accounts.partials.list', compact('accounts', 'totalBalance'));
        }

        return view('accounts.index', compact('accounts', 'totalBalance'));
    }

    public function show(Account $account, Request $request)
    {
        $user = $this->getActiveUser();
        if ($account->user_id !== $user->id) {
            abort(403);
        }

        $transactions = Transaction::where('user_id', $user->id)
            ->where(function ($q) use ($account) {
                $q->where('account_id', $account->id)
                  ->orWhere('destination_account_id', $account->id);
            })
            ->with(['category', 'account'])
            ->orderBy('transacted_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        if ($request->header('HX-Request') && $request->has('feed_only')) {
            return view('accounts.partials.transactions_feed', compact('account', 'transactions'));
        }

        return view('accounts.show', compact('account', 'transactions'));
    }

    public function transactionsFeed(Account $account, Request $request)
    {
        $user = $this->getActiveUser();
        if ($account->user_id !== $user->id) {
            abort(403);
        }

        $transactions = Transaction::where('user_id', $user->id)
            ->where(function ($q) use ($account) {
                $q->where('account_id', $account->id)
                  ->orWhere('destination_account_id', $account->id);
            })
            ->with(['category', 'account'])
            ->orderBy('transacted_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('accounts.partials.transactions_feed', compact('account', 'transactions'));
    }

    public function create()
    {
        return view('accounts.modal_create');
    }

    public function store(Request $request)
    {
        $user = $this->getActiveUser();

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'type' => 'required|in:bank,cash,savings,credit',
            'balance' => 'required|numeric',
            'currency' => 'required|in:EUR,USD',
            'color' => 'required|string|max:20',
        ]);

        $icons = [
            'bank' => 'building-library',
            'cash' => 'wallet',
            'savings' => 'banknotes',
            'credit' => 'credit-card',
        ];

        Account::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'balance' => $validated['balance'],
            'currency' => $validated['currency'],
            'color' => $validated['color'],
            'icon' => $icons[$validated['type']] ?? 'wallet',
            'is_active' => true,
        ]);

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'accountCreated' => true,
                    'closeModal' => true,
                    'showToast' => __('Account added successfully!'),
                ]));
        }

        return redirect()->route('accounts.index')->with('success', __('Account created successfully!'));
    }

    public function edit(Account $account)
    {
        $user = $this->getActiveUser();
        if ($account->user_id !== $user->id) {
            abort(403);
        }

        return view('accounts.modal_edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $user = $this->getActiveUser();
        if ($account->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'type' => 'required|in:bank,cash,savings,credit',
            'balance' => 'required|numeric',
            'currency' => 'required|in:EUR,USD',
            'color' => 'required|string|max:20',
        ]);

        $icons = [
            'bank' => 'building-library',
            'cash' => 'wallet',
            'savings' => 'banknotes',
            'credit' => 'credit-card',
        ];

        $account->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'balance' => $validated['balance'],
            'currency' => $validated['currency'],
            'color' => $validated['color'],
            'icon' => $icons[$validated['type']] ?? 'wallet',
        ]);

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'accountUpdated' => true,
                    'closeModal' => true,
                    'showToast' => __('Account updated successfully!'),
                ]));
        }

        return redirect()->route('accounts.index')->with('success', __('Account updated successfully!'));
    }

    public function destroy(Account $account, Request $request)
    {
        $user = $this->getActiveUser();
        if ($account->user_id !== $user->id) {
            abort(403);
        }

        if ($user->accounts()->count() <= 1) {
            if ($request->header('HX-Request')) {
                return response('')
                    ->header('HX-Trigger', json_encode([
                        'showToast' => __('Cannot delete the only account!'),
                    ]));
            }
            return redirect()->back()->with('error', __('Cannot delete the only account!'));
        }

        $account->transactions()->delete();
        $account->delete();

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'accountDeleted' => true,
                    'closeModal' => true,
                    'showToast' => __('Account deleted.'),
                ]));
        }

        return redirect()->route('accounts.index')->with('success', __('Account deleted.'));
    }
}
