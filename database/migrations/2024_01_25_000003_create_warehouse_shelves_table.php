<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouse_shelves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('warehouse_locations')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('level');
            $table->integer('capacity');
            $table->string('location_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_shelves');
    }
};
