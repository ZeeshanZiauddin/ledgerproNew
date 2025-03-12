<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('flight_details', function (Blueprint $table) {
            $table->string('fit_status')->nullable()->after('to');
            $table->integer('record_no')->after('id')->nullable();

            // Modify 'dep' and 'arr' columns to be nullable
            $table->time('dep')->nullable()->change();
            $table->time('arr')->nullable()->change();
            $table->string('airline')->nullable()->change();
            $table->string('flight')->nullable()->change();
            $table->string('class')->nullable()->change();
            $table->date('date')->nullable()->change();
            $table->string('from')->nullable()->change();
            $table->string('to')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('flight_details', function (Blueprint $table) {
            $table->dropColumn(['fit_status', 'record_no']);

            // Revert 'dep' and 'arr' to NOT NULL (assuming original state)
            $table->time('dep')->nullable(false)->change();
            $table->time('arr')->nullable(false)->change();
        });
    }
};