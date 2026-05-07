<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('image')->constrained('medicine_categories')->nullOnDelete();
        });

        $categories = DB::table('medicines')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        foreach ($categories as $categoryName) {
            $categoryId = DB::table('medicine_categories')->insertGetId([
                'name' => $categoryName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('medicines')
                ->where('category', $categoryName)
                ->update(['category_id' => $categoryId]);
        }
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('medicine_categories');
    }
};
