<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->decimal('tkt_sale', 15, 2)->after('contact_address');
            $table->decimal('tkt_cost', 15, 2)->after('tkt_sale');
            $table->decimal('other_sale', 15, 2)->after('tkt_cost');
            $table->decimal('other_cost', 15, 2)->after('other_sale');
            $table->decimal('sale_return', 15, 2)->after('other_cost');
            $table->decimal('pur_return', 15, 2)->after('sale_return');
            $table->decimal('total_receipt', 15, 2)->after('pur_return');
            $table->decimal('refund_paid', 15, 2)->after('total_receipt');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn([
                'tkt_sale',
                'tkt_cost',
                'other_sale',
                'other_cost',
                'sale_return',
                'pur_return',
                'total_receipt',
                'refund_paid'
            ]);
        });
    }
};