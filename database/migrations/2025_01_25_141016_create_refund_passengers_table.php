<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundPassengersTable extends Migration
{
    public function up(): void
    {
        Schema::create('refund_passengers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id');
            $table->string('record_no')->nullable();
            $table->string('name')->nullable();
            $table->decimal('sale', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('ref_to_cus', 10, 2)->nullable();
            $table->decimal('ref_to_vendor', 10, 2)->nullable();
            $table->decimal('sale_return', 10, 2)->nullable();
            $table->decimal('pur_return', 10, 2)->nullable();
            $table->date('apply_date')->nullable();
            $table->date('approve_date')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_passengers');
    }
}