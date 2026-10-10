<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
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

    public static function getSectionMeta(): array
    {
        return [
            'balance_card' => [
                'id' => 'balance_card',
                'title' => __('Total Balance & Budget'),
                'description' => __('Total balance, target savings and monthly budget status'),
                'icon' => 'wallet',
                'color' => '#6366f1',
            ],
            'daily_limit' => [
                'id' => 'daily_limit',
                'title' => __('Daily Spending Limit'),
                'description' => __('Safe to spend today, daily allowance and progress bar'),
                'icon' => 'sparkles',
                'color' => '#10b981',
            ],
            'top_categories' => [
                'id' => 'top_categories',
                'title' => __('Top Categories'),
                'description' => __('Horizontal categories spending scroll and shortcuts'),
                'icon' => 'squares',
                'color' => '#f59e0b',
            ],
            'recent_transactions' => [
                'id' => 'recent_transactions',
                'title' => __('Recent Transactions'),
                'description' => __('Search bar, filter pills and recent transactions list'),
                'icon' => 'clock',
                'color' => '#06b6d4',
            ],
        ];
    }

    public function index(Request $request)
    {
        $user = $this->getActiveUser();
        $sections = $user->getDashboardSections();
        $sectionMeta = self::getSectionMeta();

        return view('settings.index', compact('user', 'sections', 'sectionMeta'));
    }

    public function updateDashboardSections(Request $request)
    {
        $user = $this->getActiveUser();
        $validIds = ['balance_card', 'daily_limit', 'top_categories', 'recent_transactions'];

        $rawSections = $request->input('sections', []);
        $cleaned = [];

        if (is_array($rawSections)) {
            foreach ($rawSections as $sec) {
                if (isset($sec['id']) && in_array($sec['id'], $validIds, true)) {
                    $cleaned[] = [
                        'id' => $sec['id'],
                        'enabled' => filter_var($sec['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ];
                }
            }
        }

        if (!empty($cleaned)) {
            $user->update(['dashboard_sections' => $cleaned]);
        }

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'dashboardSectionsUpdated' => true,
                    'showToast' => __('Settings saved successfully!'),
                ]));
        }

        return redirect()->route('settings.index')->with('success', __('Settings saved successfully!'));
    }

    public function resetDashboardSections(Request $request)
    {
        $user = $this->getActiveUser();
        $user->update(['dashboard_sections' => null]);

        if ($request->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'dashboardSectionsUpdated' => true,
                    'showToast' => __('Settings reset to default!'),
                ]));
        }

        return redirect()->route('settings.index')->with('success', __('Settings reset to default!'));
    }
}
