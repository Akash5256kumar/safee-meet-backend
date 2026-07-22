<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Structured feature catalog powering the plan comparison table.
     *
     *   features       — the comparable rows (one per capability), grouped and
     *                    typed as boolean (✓/✗) or limit (a value like "3" /
     *                    "Unlimited").
     *   plan_feature   — which features each plan has: `included` for boolean
     *                    rows, `value` for limit rows.
     */
    public function up(): void
    {
        if (!Schema::hasTable('features')) {
            Schema::create('features', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('description')->nullable();
                $table->enum('type', ['boolean', 'limit'])->default('boolean');
                $table->string('group')->default('General');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('plan_feature')) {
            Schema::create('plan_feature', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
                $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
                $table->boolean('included')->default(false);
                $table->string('value')->nullable();
                $table->timestamps();

                $table->unique(['plan_id', 'feature_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_feature');
        Schema::dropIfExists('features');
    }
};
