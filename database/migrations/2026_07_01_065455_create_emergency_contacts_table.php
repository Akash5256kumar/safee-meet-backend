<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('emergency_contacts')) {
            Schema::create('emergency_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('full_name');
                $table->string('relationship');
                $table->string('phone_number', 20);

                $table->timestamps();
            });
            return;
        }

        // A pre-existing deployment already had this table under an older
        // schema (ULID ids, encrypted phone/email columns, no plain
        // phone_number). Reconcile it to the shape this app's model expects
        // instead of dropping/recreating — this table holds live user data.
        if (!Schema::hasColumn('emergency_contacts', 'phone_number')) {
            Schema::table('emergency_contacts', function (Blueprint $table) {
                $table->string('phone_number', 20)->nullable()->after('relationship');
            });

            DB::table('emergency_contacts')->orderBy('id')->each(function ($row) {
                if (empty($row->phone_encrypted)) {
                    return;
                }
                try {
                    $phone = decrypt($row->phone_encrypted);
                } catch (\Throwable) {
                    $phone = null;
                }
                if ($phone) {
                    DB::table('emergency_contacts')->where('id', $row->id)->update([
                        'phone_number' => substr($phone, 0, 20),
                    ]);
                }
            });

            // Leave no NULLs behind — a later migration tightens this column to NOT NULL.
            DB::table('emergency_contacts')->whereNull('phone_number')->update(['phone_number' => '']);
        }

        Schema::table('emergency_contacts', function (Blueprint $table) {
            foreach ([
                'phone_encrypted', 'phone_hash', 'email_encrypted', 'email_hash',
                'priority', 'can_receive_location', 'is_verified', 'verified_at',
                'created_by_user_id', 'updated_by_user_id', 'deleted_at',
            ] as $column) {
                if (Schema::hasColumn('emergency_contacts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
