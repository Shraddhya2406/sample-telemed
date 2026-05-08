<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('prescriptions')) {
            Schema::create('prescriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
                $table->json('medicines')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('doctor_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('doctor_profiles', 'qualification')) {
                $table->string('qualification')->nullable()->after('license_number');
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }

            if (! Schema::hasColumn('appointments', 'symptoms')) {
                $table->text('symptoms')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('appointments', 'diagnosis')) {
                $table->text('diagnosis')->nullable()->after('symptoms');
            }

            if (! Schema::hasColumn('appointments', 'advice')) {
                $table->text('advice')->nullable()->after('diagnosis');
            }
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('prescriptions', 'diagnosis')) {
                $table->text('diagnosis')->nullable()->after('notes');
            }
        });

        if (! Schema::hasTable('prescription_items')) {
            Schema::create('prescription_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
                $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
                $table->string('dosage');
                $table->string('duration');
                $table->text('instructions')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('doctor_availabilities')) {
            Schema::create('doctor_availabilities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
                $table->string('day_of_week', 16);
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['doctor_id', 'day_of_week', 'start_time', 'end_time'], 'doctor_availability_unique_slot');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_availabilities');
        Schema::dropIfExists('prescription_items');

        Schema::table('prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('prescriptions', 'diagnosis')) {
                $table->dropColumn('diagnosis');
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            foreach (['notes', 'symptoms', 'diagnosis', 'advice'] as $column) {
                if (Schema::hasColumn('appointments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('doctor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('doctor_profiles', 'qualification')) {
                $table->dropColumn('qualification');
            }
        });
    }
};
