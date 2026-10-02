<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardApiController extends Controller
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
        $month = $request->get('month');
        $summary = $this->budgetService->getDashboardSummary($user, $month);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
