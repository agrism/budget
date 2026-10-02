<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $data = $this->budgetService->getBudgetsAnalytics($user, $month);

        if ($request->header('HX-Request')) {
            return view('budgets.partials.content', compact('data'));
        }

        return view('budgets.index', compact('data'));
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
