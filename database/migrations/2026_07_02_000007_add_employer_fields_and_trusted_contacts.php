<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // account_type already exists on this deployment (enum includes
            // 'admin'/'support' too) — don't narrow/recreate it.
            if (!Schema::hasColumn('users', 'account_type')) {
                $table->enum('account_type', ['normal', 'employer'])->default('normal');
            }
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'employer_code')) {
                $table->string('employer_code')->nullable(); // e.g. EMP-123456
            }
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable();
            }
        });

        if (!Schema::hasTable('trusted_contacts')) {
            Schema::create('trusted_contacts', function (Blueprint $table) {
                $table->id();
                // users.id is a char(26) ULID on this deployment, not bigint.
                $table->char('user_id', 26);
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->string('name');
                $table->string('phone');
                $table->string('relationship')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_contacts');
        Schema::table('users', function (Blueprint $table) {
            foreach (['company_name', 'employer_code', 'job_title'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
