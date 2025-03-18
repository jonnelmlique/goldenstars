<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('model');
            $table->string('serial');
            $table->foreignId('department_id')->constrained();
            $table->foreignId('building_id')->constrained();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->boolean('is_defective')->default(false);
            $table->date('date_transferred')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
