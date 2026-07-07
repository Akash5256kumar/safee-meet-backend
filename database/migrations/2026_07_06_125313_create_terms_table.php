<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('terms')) {
            return;
        }

        // "admins" is created by a later migration (chronologically after
        // this one), so it may not exist yet — add the FK only if it does,
        // rather than failing on migration order.
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->longText('content')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('admins')) {
            Schema::table('terms', function (Blueprint $table) {
                $table->foreign('updated_by')->references('id')->on('admins')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
