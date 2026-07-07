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
            // users.id is a char(26) ULID on this deployment, not bigint.
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
