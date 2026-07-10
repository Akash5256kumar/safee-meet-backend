<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per (searcher, member) pair — repeat searches for the same
     * member update this row instead of inserting a new one, so counts
     * and "recently searched" ordering stay deduped at the DB level.
     * Full per-search-attempt history (including the raw PIN/QR query
     * text) is still logged separately in `search_history`.
     */
    public function up(): void
    {
        if (Schema::hasTable('member_search_counts')) {
            return;
        }

        Schema::create('member_search_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('searcher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('search_count')->default(1);
            $table->timestamp('last_searched_at');
            $table->timestamps();

            $table->unique(['searcher_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_search_counts');
    }
};
