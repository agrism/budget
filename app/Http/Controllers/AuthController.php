<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            if (Auth::user()->locale) {
                Session::put('locale', Auth::user()->locale);
            }
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5|confirmed',
            'currency' => 'nullable|in:EUR,USD',
            'locale' => 'nullable|in:en,lv,ru',
        ]);

        $currency = $validated['currency'] ?? 'EUR';
        $locale = $validated['locale'] ?? 'en';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'currency' => $currency,
            'locale' => $locale,
        ]);

        Auth::login($user);
        Session::put('locale', $locale);

        // Setup default user accounts & categories
        $checking = Account::create([
            'user_id' => $user->id,
            'name' => 'Main Checking',
            'type' => 'bank',
            'balance' => 0.00,
            'currency' => $currency,
            'color' => '#6366f1',
            'icon' => 'building-library',
            'is_active' => true,
        ]);

        $savings = Account::create([
            'user_id' => $user->id,
            'name' => 'Savings',
            'type' => 'savings',
            'balance' => 0.00,
            'currency' => $currency,
            'color' => '#10b981',
            'icon' => 'banknotes',
            'is_active' => true,
        ]);

        $cash = Account::create([
            'user_id' => $user->id,
            'name' => 'Cash Wallet',
            'type' => 'cash',
            'balance' => 0.00,
            'currency' => $currency,
            'color' => '#f59e0b',
            'icon' => 'wallet',
            'is_active' => true,
        ]);

        $categories = [
            ['name' => 'Groceries', 'type' => 'expense', 'icon' => 'shopping-cart', 'color' => '#10b981', 'sort_order' => 1, 'budget' => 500.00],
            ['name' => 'Dining & Drinks', 'type' => 'expense', 'icon' => 'cake', 'color' => '#f59e0b', 'sort_order' => 2, 'budget' => 250.00],
            ['name' => 'Housing & Utilities', 'type' => 'expense', 'icon' => 'home', 'color' => '#3b82f6', 'sort_order' => 3, 'budget' => 800.00],
            ['name' => 'Transport & Gas', 'type' => 'expense', 'icon' => 'truck', 'color' => '#06b6d4', 'sort_order' => 4, 'budget' => 150.00],
            ['name' => 'Entertainment', 'type' => 'expense', 'icon' => 'film', 'color' => '#ec4899', 'sort_order' => 5, 'budget' => 200.00],
            ['name' => 'Salary', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#22c55e', 'sort_order' => 6, 'budget' => 0],
        ];

        $currentMonth = Carbon::now()->format('Y-m');
        foreach ($categories as $cat) {
            $createdCat = Category::create([
                'user_id' => $user->id,
                'name' => $cat['name'],
                'type' => $cat['type'],
                'icon' => $cat['icon'],
                'color' => $cat['color'],
                'sort_order' => $cat['sort_order'],
            ]);

            if ($cat['budget'] > 0) {
                Budget::create([
                    'user_id' => $user->id,
                    'category_id' => $createdCat->id,
                    'monthly_limit' => $cat['budget'],
                    'period_month' => $currentMonth,
                    'alert_threshold' => 80,
                ]);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Welcome to Budget Tracker!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function setLocale(Request $request, string $locale)
    {
        if (in_array($locale, ['en', 'lv', 'ru'])) {
            Session::put('locale', $locale);
            if (Auth::check()) {
                Auth::user()->update(['locale' => $locale]);
            }
        }

        return redirect()->back();
    }

    public function setCurrency(Request $request)
    {
        $validated = $request->validate([
            'currency' => 'required|in:EUR,USD',
        ]);

        if (Auth::check()) {
            Auth::user()->update(['currency' => $validated['currency']]);
        }
        Session::put('currency', $validated['currency']);

        return redirect()->back();
    }
}
