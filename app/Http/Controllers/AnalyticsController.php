<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
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
        $type = $request->get('type', 'expense');

        $data = $this->budgetService->getAnalyticsData($user, $month, $type);

        if ($request->header('HX-Request')) {
            return view('analytics.partials.content', compact('data'));
        }

        return view('analytics.index', compact('data'));
    }
}
