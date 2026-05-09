<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('doctor_profiles', 'consultation_fee')) {
                $table->decimal('consultation_fee', 12, 2)->nullable()->after('qualification');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('doctor_profiles', 'consultation_fee')) {
                $table->dropColumn('consultation_fee');
            }
        });
    }
};
