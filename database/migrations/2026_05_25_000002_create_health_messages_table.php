<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('health_conversations')->cascadeOnDelete();
            $table->string('sender_type');
            $table->text('message');
            $table->timestamp('created_at')->nullable();

            $table->index(['conversation_id', 'sender_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_messages');
    }
};
