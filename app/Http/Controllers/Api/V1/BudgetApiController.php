<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\User;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetApiController extends Controller
{
    public function __construct(protected BudgetService $budgetService) {}

    protected function getActiveUser(Request $request): User
    {
        $user = $request->user() ?? Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        return $user;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->getActiveUser($request);
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $data = $this->budgetService->getBudgetsAnalytics($user, $month);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function storeOrUpdate(Request $request): JsonResponse
    {
        $user = $this->getActiveUser($request);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'monthly_limit' => 'required|numeric|min:0',
            'period_month' => 'required|string|size:7',
        ]);

        $budget = Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $validated['category_id'],
                'period_month' => $validated['period_month'],
            ],
            [
                'monthly_limit' => $validated['monthly_limit'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Budget limit updated successfully',
            'data' => $budget->load('category'),
        ]);
    }
}
