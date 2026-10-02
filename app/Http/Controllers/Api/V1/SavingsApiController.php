<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingsApiController extends Controller
{
    public function __construct(protected BudgetService $budgetService) {}

    protected function getActiveUser(?Request $request = null): User
    {
        $user = $request?->user() ?? Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        return $user;
    }

    public function show(Request $request): JsonResponse
    {
        $user = $this->getActiveUser($request);
        $summary = $this->budgetService->getDashboardSummary($user);

        return response()->json([
            'success' => true,
            'data' => [
                'savings_target_percentage' => $summary['savings_target_percentage'],
                'target_savings_amount' => $summary['target_savings_amount'],
                'effective_income' => $summary['effective_income'],
                'monthly_spendable_budget' => $summary['monthly_spendable_budget'],
                'daily_spending_limit' => $summary['daily_spending_limit'],
                'daily_base_limit' => $summary['daily_base_limit'],
                'spent_today' => $summary['spent_today'],
                'today_remaining_daily' => $summary['today_remaining_daily'],
                'today_is_over' => $summary['today_is_over'],
                'days_in_month' => $summary['days_in_month'],
                'days_remaining_in_month' => $summary['days_remaining'],
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->getActiveUser($request);

        $validated = $request->validate([
            'savings_target_percentage' => 'required|numeric|min:0|max:95',
            'expected_monthly_income' => 'nullable|numeric|min:0',
        ]);

        $user->update([
            'savings_target_percentage' => $validated['savings_target_percentage'],
            'expected_monthly_income' => !empty($validated['expected_monthly_income']) ? $validated['expected_monthly_income'] : null,
        ]);

        $summary = $this->budgetService->getDashboardSummary($user->fresh());

        return response()->json([
            'success' => true,
            'message' => 'Savings target updated successfully',
            'data' => [
                'savings_target_percentage' => $summary['savings_target_percentage'],
                'target_savings_amount' => $summary['target_savings_amount'],
                'daily_spending_limit' => $summary['daily_spending_limit'],
                'monthly_spendable_budget' => $summary['monthly_spendable_budget'],
            ],
        ]);
    }
}
