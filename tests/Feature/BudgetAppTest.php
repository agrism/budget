<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\BudgetAppSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetAppTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BudgetAppSeeder::class);
        $this->user = User::first();
    }

    public function test_guest_is_redirected_to_login_and_does_not_see_fake_data(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));

        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertDontSee('Alex Rivera');
        $loginResponse->assertDontSee('Total Balance');
        $loginResponse->assertDontSee('Recent Transactions');
    }

    public function test_guest_cannot_access_protected_pages(): void
    {
        $this->get('/budgets')->assertRedirect(route('login'));
        $this->get('/accounts')->assertRedirect(route('login'));
        $this->get('/categories')->assertRedirect(route('login'));
        $this->get('/analytics')->assertRedirect(route('login'));
    }

    public function test_unauthenticated_api_requests_return_401(): void
    {
        $this->getJson('/api/v1/dashboard')->assertStatus(401);
        $this->getJson('/api/v1/transactions')->assertStatus(401);
        $this->getJson('/api/v1/categories')->assertStatus(401);
        $this->getJson('/api/v1/savings')->assertStatus(401);
        $this->getJson('/api/v1/analytics')->assertStatus(401);
    }

    public function test_dashboard_screen_can_be_rendered(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Total Balance');
        $response->assertSee('Monthly Budget Status');
        $response->assertSee('Recent Transactions');
    }

    public function test_dashboard_balance_card_partial_htmx(): void
    {
        $this->actingAs($this->user);
        $response = $this->withHeaders(['HX-Request' => 'true'])->get('/dashboard/balance-card');
        $response->assertStatus(200);
        $response->assertSee('Total Balance');
        $response->assertSee('Top Categories');
    }

    public function test_recent_transactions_filtering_htmx(): void
    {
        $this->actingAs($this->user);
        $response = $this->withHeaders(['HX-Request' => 'true'])->get('/dashboard/recent-transactions?type=expense');
        $response->assertStatus(200);
    }

    public function test_add_transaction_modal_htmx(): void
    {
        $this->actingAs($this->user);
        $response = $this->withHeaders(['HX-Request' => 'true'])->get('/transactions/create');
        $response->assertStatus(200);
        $response->assertSee('Add Transaction');
        $response->assertSee('Enter Amount');
    }

    public function test_store_transaction_via_htmx_triggers_events(): void
    {
        $this->actingAs($this->user);
        $account = Account::first();
        $category = Category::where('type', 'expense')->first();
        $initialBalance = (float) $account->balance;

        $response = $this->withHeaders([
            'HX-Request' => 'true',
        ])->post('/transactions', [
            'amount' => '45.50',
            'type' => 'expense',
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transacted_at' => now()->format('Y-m-d H:i:s'),
            'note' => 'Test Coffee',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('HX-Trigger');
        $this->assertDatabaseHas('transactions', [
            'amount' => 45.50,
            'note' => 'Test Coffee',
        ]);

        $this->assertEquals($initialBalance - 45.50, (float) $account->fresh()->balance);
    }

    public function test_edit_transaction_modal_htmx(): void
    {
        $this->actingAs($this->user);
        $tx = $this->user->transactions()->first();
        $this->assertNotNull($tx);

        $response = $this->withHeaders(['HX-Request' => 'true'])->get(route('transactions.edit', $tx));
        $response->assertStatus(200);
        $response->assertSee('Transaction Details');
        $response->assertSee('Edit');
        $response->assertSee('Cancel');
        $response->assertSee('Save Changes');
    }

    public function test_update_transaction_via_htmx_adjusts_balances(): void
    {
        $this->actingAs($this->user);
        $account = $this->user->accounts()->first();
        $category = $this->user->categories()->where('type', 'expense')->first();

        // Create transaction of 50.00
        $tx = \App\Models\Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50.00,
            'transacted_at' => now(),
            'note' => 'Original Note',
        ]);
        $account->decrement('balance', 50.00);

        $balanceBeforeUpdate = (float) $account->fresh()->balance;

        // Update to 80.00 (expense increased by 30)
        $response = $this->withHeaders(['HX-Request' => 'true'])->put(route('transactions.update', $tx), [
            'amount' => '80.00',
            'type' => 'expense',
            'account_id' => $account->id,
            'category_id' => $category->id,
            'note' => 'Updated Note',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('HX-Trigger');

        $this->assertDatabaseHas('transactions', [
            'id' => $tx->id,
            'amount' => 80.00,
            'note' => 'Updated Note',
        ]);

        // Balance should have decreased by another 30.00
        $this->assertEquals($balanceBeforeUpdate - 30.00, (float) $account->fresh()->balance);
    }

    public function test_budgets_page_can_be_rendered(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/budgets');
        $response->assertStatus(200);
        $response->assertSee('Budgets', false);
        $response->assertSee('Total Monthly Budget');
    }

    public function test_analytics_page_can_be_rendered(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/analytics');
        $response->assertStatus(200);
        $response->assertSee('Spending Analytics');
        $response->assertSee('6-Month Trend');
    }

    public function test_stage2_flutter_api_dashboard_endpoint(): void
    {
        $this->actingAs($this->user);
        $response = $this->getJson('/api/v1/dashboard');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_balance',
                'monthly_income',
                'monthly_expense',
                'savings_rate',
                'categories',
                'recent_transactions',
            ],
        ]);
    }

    public function test_stage2_flutter_api_transactions_endpoint(): void
    {
        $this->actingAs($this->user);
        $response = $this->getJson('/api/v1/transactions');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data',
                'current_page',
                'total',
            ],
        ]);
    }

    public function test_user_can_register_with_currency_and_locale(): void
    {
        $response = $this->post('/register', [
            'name' => 'Janis Berzins',
            'email' => 'janis@example.com',
            'password' => '12345',
            'password_confirmation' => '12345',
            'currency' => 'EUR',
            'locale' => 'lv',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'janis@example.com',
            'currency' => 'EUR',
            'locale' => 'lv',
        ]);
        $this->assertDatabaseHas('accounts', [
            'name' => 'Main Checking',
            'currency' => 'EUR',
        ]);
    }

    public function test_user_can_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'demo@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_locale_switching(): void
    {
        $response = $this->get('/locale/lv');
        $response->assertSessionHas('locale', 'lv');

        $response = $this->get('/locale/ru');
        $response->assertSessionHas('locale', 'ru');
    }

    public function test_currency_switching(): void
    {
        $this->actingAs($this->user);
        $response = $this->post('/currency/set', [
            'currency' => 'EUR',
        ]);

        $this->assertEquals('EUR', $this->user->fresh()->currency);
    }

    public function test_accounts_page_and_creation(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/accounts');
        $response->assertStatus(200);

        // Create account
        $response = $this->withHeaders(['HX-Request' => 'true'])->post('/accounts', [
            'name' => 'Revolut Card',
            'type' => 'bank',
            'balance' => '0.00',
            'currency' => 'EUR',
            'color' => '#3b82f6',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('accounts', [
            'name' => 'Revolut Card',
            'balance' => 0.00,
        ]);

        $account = Account::where('name', 'Revolut Card')->first();

        // Edit modal
        $response = $this->get("/accounts/{$account->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Edit Account');

        // Update account
        $response = $this->withHeaders(['HX-Request' => 'true'])->put("/accounts/{$account->id}", [
            'name' => 'Revolut Pro',
            'type' => 'bank',
            'balance' => '100.00',
            'currency' => 'EUR',
            'color' => '#10b981',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Revolut Pro',
            'balance' => 100.00,
        ]);

        // Delete account
        $response = $this->withHeaders(['HX-Request' => 'true'])->delete("/accounts/{$account->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_account_show_and_transactions_infinite_feed(): void
    {
        $this->actingAs($this->user);
        $account = $this->user->accounts()->first();
        $this->assertNotNull($account);

        // Test full page show
        $response = $this->get(route('accounts.show', $account));
        $response->assertStatus(200);
        $response->assertSee('Account Transactions');
        $response->assertSee($account->name);

        // Test transactions feed pagination endpoint
        $response = $this->withHeaders(['HX-Request' => 'true'])->get(route('accounts.transactions_feed', ['account' => $account, 'page' => 1]));
        $response->assertStatus(200);
    }

    public function test_categories_page_and_crud(): void
    {
        $this->actingAs($this->user);

        // 1. List categories
        $response = $this->get('/categories');
        $response->assertStatus(200);
        $response->assertSee('Categories');

        // 2. Create category via HTMX
        $response = $this->withHeaders(['HX-Request' => 'true'])->post('/categories', [
            'name' => 'Fitness & Gym',
            'type' => 'expense',
            'icon' => 'heart',
            'color' => '#ec4899',
            'monthly_limit' => '80',
        ]);
        $response->assertStatus(200);
        $response->assertHeader('HX-Trigger');
        $this->assertDatabaseHas('categories', [
            'name' => 'Fitness & Gym',
            'type' => 'expense',
            'icon' => 'heart',
        ]);
        $this->assertDatabaseHas('budgets', [
            'monthly_limit' => 80,
        ]);

        $category = Category::where('name', 'Fitness & Gym')->first();

        // 3. Edit modal
        $response = $this->get("/categories/{$category->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Edit Category');

        // 4. Update category
        $response = $this->withHeaders(['HX-Request' => 'true'])->put("/categories/{$category->id}", [
            'name' => 'Fitness & Sports',
            'type' => 'expense',
            'icon' => 'sparkles',
            'color' => '#10b981',
            'monthly_limit' => '100',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Fitness & Sports',
            'color' => '#10b981',
        ]);

        // 5. Delete category
        $response = $this->withHeaders(['HX-Request' => 'true'])->delete("/categories/{$category->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_stage2_flutter_api_categories_endpoint(): void
    {
        $this->actingAs($this->user);
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'categories' => [
                '*' => ['id', 'name', 'type', 'icon', 'color', 'sort_order', 'monthly_limit', 'transactions_count'],
            ],
        ]);
    }

    public function test_savings_settings_modal_and_update_htmx(): void
    {
        $this->actingAs($this->user);

        // 1. Open savings modal
        $response = $this->withHeaders(['HX-Request' => 'true'])->get('/savings/settings');
        $response->assertStatus(200);
        $response->assertSee('Savings Target');

        // 2. Update savings target % and baseline income
        $response = $this->withHeaders(['HX-Request' => 'true'])->post('/savings/update', [
            'savings_target_percentage' => '25',
            'expected_monthly_income' => '3000',
        ]);
        $response->assertStatus(200);
        $response->assertHeader('HX-Trigger');

        $this->user->refresh();
        $this->assertEquals(25.0, (float) $this->user->savings_target_percentage);
        $this->assertEquals(3000.0, (float) $this->user->expected_monthly_income);
    }

    public function test_stage2_flutter_api_savings_endpoints(): void
    {
        $this->actingAs($this->user);

        // 1. Get savings info
        $response = $this->getJson('/api/v1/savings');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'savings_target_percentage',
                'target_savings_amount',
                'effective_income',
                'monthly_spendable_budget',
                'daily_spending_limit',
                'spent_today',
                'today_remaining_daily',
                'days_remaining_in_month',
            ],
        ]);

        // 2. Update savings info
        $response = $this->postJson('/api/v1/savings', [
            'savings_target_percentage' => 30,
            'expected_monthly_income' => 2500,
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'savings_target_percentage' => 30,
            ],
        ]);
    }

    public function test_manual_category_is_included_in_analytics_and_api(): void
    {
        $this->actingAs($this->user);

        // 1. Create a manual category
        $manualCategory = Category::create([
            'user_id' => $this->user->id,
            'name' => 'Custom Pet Care',
            'type' => 'expense',
            'icon' => 'heart',
            'color' => '#f43f5e',
            'sort_order' => 99,
        ]);

        // 2. Check analytics web page includes manual category
        $response = $this->get('/analytics');
        $response->assertStatus(200);
        $response->assertSee('Custom Pet Care');
        $response->assertSee('Category Distribution');

        // 3. Add a transaction with the manual category
        $account = Account::where('user_id', $this->user->id)->first();
        $this->post('/transactions', [
            'amount' => '120.00',
            'type' => 'expense',
            'account_id' => $account->id,
            'category_id' => $manualCategory->id,
            'transacted_at' => now()->format('Y-m-d H:i:s'),
            'note' => 'Vet clinic visit',
        ]);

        // 4. Verify API analytics endpoint includes manual category in breakdown and donut segments
        $apiResponse = $this->getJson('/api/v1/analytics');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonStructure([
            'success',
            'data' => [
                'month',
                'formatted_month',
                'category_breakdown',
                'donut_segments',
                'monthly_trends',
            ],
        ]);

        $breakdown = collect($apiResponse->json('data.category_breakdown'));
        $this->assertTrue($breakdown->contains('name', 'Custom Pet Care'));

        $segments = collect($apiResponse->json('data.donut_segments'));
        $this->assertTrue($segments->contains('name', 'Custom Pet Care'));
    }

    public function test_transaction_date_formatting_removes_midnight_time_and_localizes_lv_month(): void
    {
        $account = Account::where('user_id', $this->user->id)->first();
        
        $currentYear = now()->year;

        // 1. Transaction with midnight 00:00:00 in October
        $txMidnight = Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 15.74,
            'transacted_at' => "{$currentYear}-10-02 00:00:00",
            'note' => 'Maxima, lasis, burkāni',
        ]);

        // 2. Transaction with specific time 14:35:00 in October
        $txWithTime = Transaction::create([
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 7.53,
            'transacted_at' => "{$currentYear}-10-05 14:35:00",
            'note' => 'Lidl vistas fileja',
        ]);

        // Check LV locale
        $this->assertEquals('Okt 02', $txMidnight->formatTransactedAt('lv'));
        $this->assertStringNotContainsString('00:00', $txMidnight->formatTransactedAt('lv'));
        $this->assertEquals('Okt 05, 14:35', $txWithTime->formatTransactedAt('lv'));

        // Check EN locale
        $this->assertEquals('Oct 02', $txMidnight->formatTransactedAt('en'));
        $this->assertStringNotContainsString('00:00', $txMidnight->formatTransactedAt('en'));
        $this->assertEquals('Oct 05, 14:35', $txWithTime->formatTransactedAt('en'));

        // Check dashboard rendering with session locale 'lv'
        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'lv'])
            ->get('/dashboard/recent-transactions');

        $response->assertStatus(200);
        $response->assertSee('Okt 02');
        $response->assertSee('Okt 05, 14:35');
        $response->assertDontSee('Oct 02, 00:00');
    }
}



