<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
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
        $month = $request->get('month');
        $summary = $this->budgetService->getDashboardSummary($user, $month);

        if ($request->header('HX-Request')) {
            return view('dashboard.partials.content', compact('summary'));
        }

        return view('dashboard.index', compact('summary'));
    }

    public function balanceCard(Request $request)
    {
        $user = $this->getActiveUser();
        $summary = $this->budgetService->getDashboardSummary($user, $request->get('month'));

        return view('dashboard.partials.balance_card', compact('summary'));
    }

    public function recentTransactions(Request $request)
    {
        $user = $this->getActiveUser();
        $search = $request->get('search');
        $type = $request->get('type');

        $query = $user->transactions()->with(['category', 'account'])->orderBy('transacted_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($type && in_array($type, ['expense', 'income', 'transfer'])) {
            $query->where('type', $type);
        }

        $transactions = $query->limit(20)->get();

        return view('dashboard.partials.transaction_list', compact('transactions'));
    }
}
