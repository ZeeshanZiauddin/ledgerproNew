<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pay_refunds', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Refund name or reference
            $table->date('date'); // Refund date
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained('banks')->cascadeOnDelete();
            $table->string('cheque_no')->nullable(); // Optional cheque number
            $table->decimal('total_amount', 15, 2); // Refund total amount
            $table->text('details')->nullable(); // Additional refund details
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete(); // User who issued refund
            $table->foreignId('modified_by')->nullable()->constrained('users')->nullOnDelete(); // User who last modified refund
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_refunds');
    }
};