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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->enum('status', ['pending', 'preparing', 'ready', 'served', 'completed'])->default('pending');
            $table->unsignedBigInteger('table_id');
            $table->unsignedBigInteger('waiter_id');
            $table->timestamps();
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('waiter_id')->references('id')->on('users')->onDelete('cascade');

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
        Schema::dropIfExists('orders');
    }
};
