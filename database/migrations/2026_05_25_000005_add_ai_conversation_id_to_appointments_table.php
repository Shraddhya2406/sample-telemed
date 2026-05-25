<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'ai_conversation_id')) {
                $table->unsignedBigInteger('ai_conversation_id')->nullable()->after('patient_id');
                $table->index('ai_conversation_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'ai_conversation_id')) {
                $table->dropIndex(['ai_conversation_id']);
                $table->dropColumn('ai_conversation_id');
            }
        });
    }
};
