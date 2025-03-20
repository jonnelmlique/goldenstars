<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->enum('status', ['open', 'in_progress', 'resolved', 'completed', 'cancelled']);
            $table->foreignId('category_id')->constrained('ticket_categories');
            $table->foreignId('requestor_id')->constrained('users');
            $table->string('requested_by')->nullable(); // Add this line
            $table->foreignId('assignee_id')->nullable()->constrained('users');
            $table->foreignId('building_id')->constrained();
            $table->foreignId('department_id')->constrained();
            $table->timestamps();
        });

        Schema::create('ticket_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_ratings');
        Schema::dropIfExists('tickets');
    }
};
