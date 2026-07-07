<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('chats') || Schema::hasTable('chat_messages')) {
            return;
        }

        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            // users.id is a char(26) ULID on this deployment, not bigint.
            $table->char('requester_id', 26);
            $table->foreign('requester_id')->references('id')->on('users')->cascadeOnDelete();
            $table->char('recipient_id', 26);
            $table->foreign('recipient_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('status', ['requested', 'accepted', 'declined', 'closed'])->default('requested');
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->cascadeOnDelete();
            $table->char('sender_id', 26);
            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chats');
    }
};
