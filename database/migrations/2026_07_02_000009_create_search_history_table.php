<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('search_history')) {
            return;
        }

        Schema::create('search_history', function (Blueprint $table) {
            $table->id();
            // This table already exists on production, so this Schema::create
            // never actually runs there (guarded above) — kept only for fresh
            // installs. NOTE: users.id was char(26) ULID when this migration
            // was written; it was later converted to bigint auto-increment
            // (see 02455bb.. / MigrateUsersToBigintId), and this table's
            // searcher_id/found_user_id were migrated along with it. A fresh
            // install today would need these as bigint unsigned, not char(26).
            $table->char('searcher_id', 26);
            $table->foreign('searcher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->char('found_user_id', 26)->nullable();
            $table->foreign('found_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('query'); // the PIN or QR code text that was searched
            $table->enum('method', ['pin', 'qr'])->default('pin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_history');
    }
};
