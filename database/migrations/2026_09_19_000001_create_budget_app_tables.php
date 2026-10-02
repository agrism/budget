<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('bank'); // bank, cash, credit, savings
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->string('color', 20)->default('#6366f1');
            $table->string('icon', 50)->default('wallet');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('expense'); // expense, income, transfer
            $table->string('icon', 50)->default('tag');
            $table->string('color', 20)->default('#10b981');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('monthly_limit', 12, 2);
            $table->string('period_month', 7); // YYYY-MM e.g. 2026-09
            $table->unsignedTinyInteger('alert_threshold')->default(80); // percentage
            $table->timestamps();

            $table->unique(['user_id', 'category_id', 'period_month']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('expense'); // expense, income, transfer
            $table->decimal('amount', 12, 2);
            $table->dateTime('transacted_at');
            $table->string('note')->nullable();
            $table->foreignId('destination_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'transacted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('accounts');
    }
};
