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
        Schema::create('menu_addon_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('inventory_item_id');
            $table->decimal('quantity', 10, 2);
            $table->decimal('additional_price', 10, 2)->nullable();
            $table->timestamps();
            $table->boolean('active_flag')->default(true);
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
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
        Schema::dropIfExists('menu_addon_items');
    }
};
