<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecordNoToCardPassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('card_passengers', function (Blueprint $table) {
            $table->string('record_no')->nullable()->after('id'); // Add after an appropriate column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('card_passengers', function (Blueprint $table) {
            $table->dropColumn('record_no');
        });
    }
}