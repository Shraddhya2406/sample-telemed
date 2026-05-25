<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('health_conversations')) {
            return;
        }

        Schema::create('health_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->text('summary')->nullable();
            $table->string('urgency_level')->default('low');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('urgency_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_conversations');
    }
};
