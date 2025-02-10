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
        Schema::create('pay_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cheque_no')->nullable();
            $table->string('ref_no')->nullable();
            $table->foreignId('bank_id')->constrained('banks')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->text('details')->nullable();
            $table->date('date')->nullable();
            $table->decimal('total', 10, 2);
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_suppliers');
    }
};