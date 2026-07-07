<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This deployment already has a "meetings" table for a live,
        // actively-used feature (multi-party, safety/SOS-linked — creator +
        // meeting_participants, different status values) that predates and
        // conflicts with this migration's host/guest design. Reconciling
        // the two data models needs a deliberate follow-up, not a blind
        // schema replace that could break the live safety flow — skip here.
        if (Schema::hasTable('meetings')) {
            return;
        }

        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // e.g. SM-7821
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();

            $table->date('meeting_date');
            $table->time('meeting_time');
            $table->string('location');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('purpose')->nullable();
            $table->string('item_or_service')->nullable();
            $table->enum('type', [
                'coffee', 'marketplace', 'property', 'business', 'freelance', 'social', 'dating', 'other',
            ])->default('other');

            $table->enum('status', [
                'scheduled', 'live', 'completed', 'cancelled', 'incident_reported',
            ])->default('scheduled');

            $table->float('trust_score_snapshot')->nullable();
            $table->timestamp('arrived_at')->nullable();

            $table->timestamps();
        });

        // Live location pings during an active meeting. Also already exists
        // on this deployment with a compatible (superset) column set — skip
        // if so rather than failing on a duplicate table.
        if (!Schema::hasTable('meeting_locations')) {
            Schema::create('meeting_locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->timestamp('recorded_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_locations');
        Schema::dropIfExists('meetings');
    }
};
