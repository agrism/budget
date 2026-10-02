<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingsController extends Controller
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

    public function modal(Request $request)
    {
        $user = $this->getActiveUser();
        $currency = $user->currency ?: 'EUR';
        $currencySymbol = BudgetService::currencySymbol($currency);
        $summary = $this->budgetService->getDashboardSummary($user);

        return view('savings.modal_settings', compact('user', 'currencySymbol', 'summary'));
    }

    public function update(Request $request)
    {
        $user = $this->getActiveUser();

        $validated = $request->validate([
            'savings_target_percentage' => 'required|numeric|min:0|max:95',
            'expected_monthly_income' => 'nullable|numeric|min:0',
        ]);

        $user->update([
            'savings_target_percentage' => $validated['savings_target_percentage'],
            'expected_monthly_income' => !empty($validated['expected_monthly_income']) ? $validated['expected_monthly_income'] : null,
        ]);

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'savingsTargetUpdated' => true,
                    'transactionCreated' => true,
                    'budgetUpdated' => true,
                    'closeModal' => true,
                    'showToast' => __('Savings target updated!'),
                ]));
        }

        return redirect()->back()->with('success', __('Savings target updated!'));
    }
}
