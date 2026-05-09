<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'consultation_fee')) {
                $table->decimal('consultation_fee', 12, 2)->default(0)->after('advice');
            }

            if (! Schema::hasColumn('appointments', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('consultation_fee');
            }

            if (! Schema::hasColumn('appointments', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('appointments', 'payment_id')) {
                $table->string('payment_id')->nullable()->index()->after('payment_method');
            }

            if (! Schema::hasColumn('appointments', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->index()->after('payment_id');
            }

            if (! Schema::hasColumn('appointments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('razorpay_order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            foreach (['paid_at', 'razorpay_order_id', 'payment_id', 'payment_method', 'payment_status', 'consultation_fee'] as $column) {
                if (Schema::hasColumn('appointments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
