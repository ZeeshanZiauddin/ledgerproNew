<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pay_refund_refund_passenger', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->after('refund_passenger_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('pay_refund_refund_passenger', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};