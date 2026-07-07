<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some deployments already have a richer "users" table (encrypted
        // email/phone, trust_score, phone_verified_at, soft deletes, etc.)
        // predating this migration. Add only what's genuinely missing —
        // no ->after() positioning either, since the columns it anchors to
        // (e.g. "email") don't exist on every deployment.
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'otp_code')) {
                $table->string('otp_code')->nullable();
            }
            if (!Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['standard', 'professional', 'admin'])->default('standard');
            }
            if (!Schema::hasColumn('users', 'verification_level')) {
                $table->enum('verification_level', ['none', 'level1', 'level2', 'professional'])->default('none');
            }
            if (!Schema::hasColumn('users', 'badge')) {
                $table->enum('badge', [
                    'none', 'level1_verified', 'level2_verified_background_checked', 'verified_professional',
                ])->default('none');
            }
            if (!Schema::hasColumn('users', 'subscription_plan')) {
                $table->enum('subscription_plan', ['free_trial', 'basic', 'premium', 'professional'])->nullable();
            }
            if (!Schema::hasColumn('users', 'subscription_status')) {
                $table->enum('subscription_status', ['trial', 'active', 'expired', 'cancelled'])->default('trial');
            }
            if (!Schema::hasColumn('users', 'safee_pin')) {
                $table->string('safee_pin')->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'dob')) {
                $table->date('dob')->nullable();
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('users', 'id_number')) {
                $table->string('id_number')->nullable();
            }
            if (!Schema::hasColumn('users', 'trust_score')) {
                $table->float('trust_score')->nullable();
            }
            if (!Schema::hasColumn('users', 'rating')) {
                $table->float('rating')->nullable();
            }
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only drop columns this migration could have added itself —
            // never drop trust_score/phone_verified_at/deleted_at, which may
            // predate this migration on some deployments.
            foreach ([
                'phone', 'otp_code', 'role', 'verification_level', 'badge',
                'subscription_plan', 'subscription_status', 'safee_pin',
                'dob', 'address', 'id_number', 'rating',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
