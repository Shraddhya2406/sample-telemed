<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('health_conversations')) {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM health_conversations'))->pluck('Key_name')->all();

        Schema::table('health_conversations', function (Blueprint $table) use ($indexes) {
            if (! in_array('health_conversations_user_id_status_index', $indexes, true)) {
                $table->index(['user_id', 'status']);
            }

            if (! in_array('health_conversations_urgency_level_index', $indexes, true)) {
                $table->index('urgency_level');
            }
        });

        $foreignKeys = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'health_conversations'
              AND COLUMN_NAME = 'user_id'
              AND REFERENCED_TABLE_NAME = 'users'
        "))->pluck('CONSTRAINT_NAME')->all();

        if (! in_array('health_conversations_user_id_foreign', $foreignKeys, true)) {
            try {
                Schema::table('health_conversations', function (Blueprint $table) {
                    $table->foreign('user_id')
                        ->references('id')
                        ->on('users')
                        ->cascadeOnDelete();
                });
            } catch (QueryException $exception) {
                Log::warning('Could not add health_conversations user foreign key.', [
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('health_conversations')) {
            return;
        }

        Schema::table('health_conversations', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (Throwable) {
            }

            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['urgency_level']);
        });
    }
};
