<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardOtherSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_other_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->decimal('sale', 15, 2)->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->date('issue_date')->nullable();
            $table->unsignedBigInteger('card_id');
            $table->text('details')->nullable();  // Adding the details column as TEXT

            // Foreign key constraints
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');

            $table->timestamps();  // Added for tracking created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_other_sales');
    }
}