<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            if (! Schema::hasColumn('medicines', 'brand')) {
                $table->string('brand')->nullable()->after('name');
            }

            if (! Schema::hasColumn('medicines', 'composition')) {
                $table->text('composition')->nullable()->after('description');
            }

            if (! Schema::hasColumn('medicines', 'manufacturer')) {
                $table->string('manufacturer')->nullable()->after('composition');
            }

            if (! Schema::hasColumn('medicines', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('stock_quantity');
            }

            if (! Schema::hasColumn('medicines', 'sku')) {
                $table->string('sku')->nullable()->after('image');
            }
        });

        $medicines = DB::table('medicines')->select('id', 'name', 'sku')->get();

        foreach ($medicines as $medicine) {
            if (! $medicine->sku) {
                DB::table('medicines')
                    ->where('id', $medicine->id)
                    ->update([
                        'sku' => 'MED-' . str_pad((string) $medicine->id, 5, '0', STR_PAD_LEFT),
                    ]);
            }
        }

        Schema::table('medicines', function (Blueprint $table) {
            if (! $this->hasIndex('medicines', 'medicines_sku_unique')) {
                $table->unique('sku');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            if ($this->hasIndex('medicines', 'medicines_sku_unique')) {
                $table->dropUnique('medicines_sku_unique');
            }

            foreach (['brand', 'composition', 'manufacturer', 'expiry_date', 'sku'] as $column) {
                if (Schema::hasColumn('medicines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }
};
