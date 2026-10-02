<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryApiController extends Controller
{
    protected function getActiveUser(?Request $request = null): User
    {
        $user = $request?->user() ?? Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        return $user;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->getActiveUser($request);
        $currentMonth = $request->get('month', Carbon::now()->format('Y-m'));

        $categories = $user->categories()
            ->with(['budgets' => function ($q) use ($currentMonth) {
                $q->where('period_month', $currentMonth);
            }])
            ->withCount('transactions')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) {
                $budget = $cat->budgets->first();
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'type' => $cat->type,
                    'icon' => $cat->icon,
                    'color' => $cat->color,
                    'sort_order' => $cat->sort_order,
                    'monthly_limit' => $budget ? (float) $budget->monthly_limit : null,
                    'transactions_count' => $cat->transactions_count,
                ];
            });

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->getActiveUser($request);

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
            Budget::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'period_month' => Carbon::now()->format('Y-m'),
                ],
                [
                    'monthly_limit' => $validated['monthly_limit'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $user = $this->getActiveUser($request);
        if ($category->user_id && $category->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
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

        if ($validated['type'] === 'expense' && $validated['monthly_limit'] !== null) {
            Budget::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'period_month' => Carbon::now()->format('Y-m'),
                ],
                [
                    'monthly_limit' => $validated['monthly_limit'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    public function destroy(Category $category, Request $request): JsonResponse
    {
        $user = $this->getActiveUser($request);
        if ($category->user_id && $category->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $category->transactions()->update(['category_id' => null]);
        $category->budgets()->delete();
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
