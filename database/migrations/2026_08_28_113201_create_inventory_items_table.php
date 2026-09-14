<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->enum('category', ['frozen', 'chilled', 'live', 'dry'])->default('frozen');
            $table->string('species')->nullable();
            $table->decimal('quantity_kg', 10, 2)->default(0);
            $table->decimal('reorder_threshold_kg', 10, 2)->default(50);
            $table->decimal('unit_price', 10, 2);
            $table->date('caught_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
