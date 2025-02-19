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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->enum('payment_method', ['cash', 'kpay', 'wave'])->default('cash');
            $table->enum('payment_status', ['pending', 'refunded', 'completed'])->default('pending');

            $table->unsignedBigInteger('cashier_id');
            $table->unsignedBigInteger('waiter_id');
            $table->unsignedBigInteger('order_id');
            
            $table->timestamps();

            $table->foreign('cashier_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('waiter_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('payments');
    }
};
