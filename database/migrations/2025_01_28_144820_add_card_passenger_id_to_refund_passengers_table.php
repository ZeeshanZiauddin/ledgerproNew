<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refund_passengers', function (Blueprint $table) {
            $table->unsignedBigInteger('card_passenger_id')->nullable()->after('id'); // Add card_passenger_id column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('refund_passengers', function (Blueprint $table) {
            $table->dropColumn('card_passenger_id'); // Drop the column if rolling back
        });
    }
};