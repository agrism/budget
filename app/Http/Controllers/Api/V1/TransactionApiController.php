<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionApiController extends Controller
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
        $transactions = $user->transactions()
            ->with(['category', 'account'])
            ->orderBy('transacted_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->getActiveUser($request);

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

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => $transaction->load(['category', 'account']),
        ], 201);
    }

    public function destroy(Transaction $transaction, Request $request): JsonResponse
    {
        $user = $this->getActiveUser($request);

        if ($transaction->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $this->budgetService->deleteTransaction($user, $transaction->id);

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully',
        ]);
    }
}
