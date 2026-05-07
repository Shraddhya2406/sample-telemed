<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id')->index();
            $table->string('image_path');
            $table->boolean('is_thumbnail')->default(false);
            $table->timestamps();
        });

        $medicines = DB::table('medicines')
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->get(['id', 'image']);

        foreach ($medicines as $medicine) {
            DB::table('medicine_images')->insert([
                'medicine_id' => $medicine->id,
                'image_path' => $medicine->image,
                'is_thumbnail' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_images');
    }
};
