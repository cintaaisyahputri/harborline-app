<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('registration_number')->unique();
            $table->string('home_port')->nullable();
            $table->string('captain_name')->nullable();
            $table->unsignedInteger('capacity_tons')->default(0);
            $table->decimal('current_lat', 10, 6)->nullable();
            $table->decimal('current_lng', 10, 6)->nullable();
            $table->enum('status', ['at_sea', 'docked', 'maintenance'])->default('docked');
            $table->timestamp('estimated_arrival')->nullable();
            $table->timestamp('last_position_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessels');
    }
};
