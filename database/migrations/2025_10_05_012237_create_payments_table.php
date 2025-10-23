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
            
            // ID unik yang dikirim ke gateway (kamu yang generate)
            $table->string('external_id')->nullable()->unique(); 
            
            // ID referensi transaksi dari gateway (mis. transaction_id / payment_id)
            $table->string('payment_reference')->nullable()->unique();

            // Relasi ke orders
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Nama gateway yang digunakan
            $table->string('gateway'); // 'midtrans', 'xendit', dll
            
            // Jenis metode pembayaran (opsional tapi berguna)
            $table->string('payment_method')->nullable(); // 'BCA_VA', 'QRIS', 'CARD', etc.

            // Jumlah pembayaran dalam Rupiah
            $table->integer('amount');
            
            // Status pembayaran
            $table->string('status')->default('pending'); // pending, success, failed, refunded
            
            // Data mentah dari API gateway
            $table->json('meta')->nullable();

            // Timestamp created_at dan updated_at
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
