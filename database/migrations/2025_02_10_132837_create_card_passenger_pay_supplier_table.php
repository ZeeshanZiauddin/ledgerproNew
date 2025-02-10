<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('card_passenger_pay_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_passenger_id')->constrained()->onDelete('cascade');
            $table->foreignId('pay_supplier_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_passenger_pay_supplier');
    }
};