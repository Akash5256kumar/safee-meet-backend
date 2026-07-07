<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                // users.id is a char(26) ULID on this deployment, not bigint.
                $table->char('user_id', 26);
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->enum('plan', ['free_trial', 'basic', 'premium', 'professional']);
                $table->decimal('price', 8, 2)->default(0);
                $table->enum('billing_cycle', ['trial', 'monthly'])->default('monthly');
                $table->enum('status', ['trial', 'active', 'expired', 'cancelled'])->default('trial');
                $table->unsignedSmallInteger('trial_days')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('renews_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->string('stripe_customer_id')->nullable();
                $table->string('stripe_subscription_id')->nullable();
                $table->timestamps();
            });
        }

        // subscription_plans already exists on some deployments (different
        // catalog schema: price_cents/currency/code instead of
        // monthly_price/yearly_price/features/icon/color/sort_order). Both
        // the admin panel and this API read/write the new column names, so
        // add what's missing instead of recreating the table.
        if (!Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('monthly_price', 8, 2);
                $table->decimal('yearly_price', 8, 2);
                $table->json('features')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('subscription_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('subscription_plans', 'monthly_price')) {
                    $table->decimal('monthly_price', 8, 2)->default(0);
                }
                if (!Schema::hasColumn('subscription_plans', 'yearly_price')) {
                    $table->decimal('yearly_price', 8, 2)->default(0);
                }
                if (!Schema::hasColumn('subscription_plans', 'features')) {
                    $table->json('features')->nullable();
                }
                if (!Schema::hasColumn('subscription_plans', 'icon')) {
                    $table->string('icon')->nullable();
                }
                if (!Schema::hasColumn('subscription_plans', 'color')) {
                    $table->string('color')->nullable();
                }
                if (!Schema::hasColumn('subscription_plans', 'sort_order')) {
                    $table->unsignedSmallInteger('sort_order')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
