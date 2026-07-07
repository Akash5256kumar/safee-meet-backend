<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('meeting_reviews')) {
            return;
        }

        Schema::create('meeting_reviews', function (Blueprint $table) {
            $table->id();
            // meetings.id and users.id are char(26) ULIDs on this deployment, not bigint.
            $table->char('meeting_id', 26);
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->char('reviewer_id', 26); // who wrote it
            $table->foreign('reviewer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->char('reviewee_id', 26); // who it's about
            $table->foreign('reviewee_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();

            // Sub-ratings shown on the Reviews & Ratings screen (Punctual / Trustworthy / Responsive)
            $table->boolean('punctual')->nullable();
            $table->boolean('trustworthy')->nullable();
            $table->boolean('responsive')->nullable();

            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_reviews');
    }
};
