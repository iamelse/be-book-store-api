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
            $table->string('external_id')->nullable()->unique(); 
            $table->string('payment_reference')->nullable()->unique();
            $table->text('payment_url')->nullable();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('gateway');
            $table->string('payment_method')->nullable();
            $table->integer('amount');
            $table->string('status')->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
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
