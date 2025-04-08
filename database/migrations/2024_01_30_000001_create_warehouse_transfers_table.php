<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouse_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('warehouse_inventory')->onDelete('cascade');
            $table->string('from_location');
            $table->string('to_location');
            $table->integer('quantity');
            $table->dateTime('transfer_date');  // Changed from date to dateTime
            $table->dateTime('received_date')->nullable();  // Changed from date to dateTime
            $table->string('status')->default('pending'); // pending, completed
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_transfers');
    }
};
