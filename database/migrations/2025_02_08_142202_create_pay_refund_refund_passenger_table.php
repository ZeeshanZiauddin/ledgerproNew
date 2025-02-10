<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pay_refund_refund_passenger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pay_refund_id')->constrained()->onDelete('cascade');
            $table->foreignId('refund_passenger_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_refund_refund_passenger');
    }
};