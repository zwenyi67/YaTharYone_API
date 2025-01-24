<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit_of_measure'); // kg, lb, liter, piece
            $table->decimal('current_stock', 10, 2);
            $table->decimal('reorder_level', 10, 2);
            $table->decimal('min_stock_level', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_perishable')->default(true);
            $table->date('expiry_date')->nullable();
            $table->unsignedBigInteger('item_category_id');
            $table->timestamps();
            $table->boolean('active_flag')->default(true);
            $table->foreign('item_category_id')->references('id')->on('inventory_item_categories')->onDelete('cascade');
            $table->unsignedBigInteger('createby');
            $table->unsignedBigInteger('updateby')->nullable();
            $table->foreign('createby')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('updateby')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
