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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'preparing', 'ready'])->default('pending');
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('order_id');
            $table->timestamps();
            $table->foreign('menu_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            $table->unsignedBigInteger('createby');
            $table->unsignedBigInteger('updateby')->nullable();
            $table->foreign('createby')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updateby')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
