<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('incidents')) {
            return;
        }

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            // users.id and meetings.id are char(26) ULIDs on this deployment, not bigint.
            $table->char('reporter_user_id', 26);
            $table->foreign('reporter_user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->char('meeting_id', 26)->nullable();
            $table->foreign('meeting_id')->references('id')->on('meetings')->nullOnDelete();

            $table->enum('type', [
                'fake_user', 'fraud', 'harassment', 'sos', 'general_incident',
            ]);

            $table->text('description')->nullable();

            // SOS-specific snapshot data
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('emergency_contacts_notified')->nullable();

            $table->enum('status', ['open', 'investigating', 'resolved'])->default('open');
            $table->char('resolved_by', 26)->nullable();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
