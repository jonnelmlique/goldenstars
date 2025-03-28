<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('warehouse_inventory');

        Schema::create('warehouse_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('item_number');
            $table->string('item_name');
            $table->string('grade');
            $table->string('batch_number');
            $table->string('location_code');
            $table->string('bom_unit');
            $table->integer('physical_inventory');
            $table->integer('physical_reserved');
            $table->integer('actual_count');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_inventory');
    }
};
