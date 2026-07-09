<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a plain lookup `email` column (replacing email_encrypted/email_hash
     * as the primary lookup going forward — those stay in place, unused, not
     * dropped) plus a few lifecycle/account-state columns. Additive only.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable();
            }
            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
            if (!Schema::hasColumn('users', 'account_suspended_at')) {
                $table->timestamp('account_suspended_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'account_deleted_at')) {
                $table->timestamp('account_deleted_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'kyc_verified_at')) {
                $table->timestamp('kyc_verified_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach (['email', 'password', 'remember_token', 'account_suspended_at', 'account_deleted_at', 'kyc_verified_at'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
