<?php

namespace App\Jobs;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SimulateOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $itemId;
    protected int $quantity;

    public function __construct(int $itemId, int $quantity)
    {
        $this->itemId = $itemId;
        $this->quantity = $quantity;
    }

    public function handle()
    {
        Log::info("SimulateOrder started for item {$this->itemId} qty {$this->quantity}");

        try {
            DB::transaction(function () {
                // Lock baris item untuk mencegah race condition
                $item = Item::where('id', $this->itemId)->lockForUpdate()->first();

                if (!$item) {
                    throw new Exception("Item {$this->itemId} tidak ditemukan");
                }

                if ($item->stock < $this->quantity) {
                    Log::warning("Stok tidak cukup untuk item {$this->itemId}. Stock sekarang: {$item->stock}");
                    return;
                }

                // Kurangi stok
                $item->decrement('stock', $this->quantity);

                Log::info("Stock item {$this->itemId} berhasil dikurangi {$this->quantity}, sisa: {$item->stock}");
            });
        } catch (Exception $e) {
            Log::error("Gagal simulasi order untuk item {$this->itemId}: " . $e->getMessage());
        }
    }
}