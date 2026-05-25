<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('health_conversations', 'medicine_suggestions')) {
                $table->json('medicine_suggestions')->nullable()->after('urgency_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('health_conversations', 'medicine_suggestions')) {
                $table->dropColumn('medicine_suggestions');
            }
        });
    }
};
