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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 10, 2); // Quantity adjusted (negative for wastage)
            $table->enum('adjustment_type', ['spoiled', 'waste', 'manual'])->default('manual');
            $table->text('reason')->nullable();
            $table->timestamp('adjustment_date');
            $table->timestamps();
            $table->boolean('active_flag')->default(true);
            $table->foreign('item_id')->references('id')->on('inventory_items')->onDelete('cascade');
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
        Schema::dropIfExists('stock_adjustments');
    }
};
