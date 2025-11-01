<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create(); // Membuat instance Faker
        $categoryIds = DB::table('item_categories')->pluck('id')->toArray(); // Mengambil ID kategori

        $items = [];

        for ($i = 0; $i < 50; $i++) {
            $items[] = [
                'title' => $faker->sentence(3), // Membuat judul buku acak (3 kata)
                'author' => $faker->name(), // Membuat nama penulis acak
                'description' => $faker->paragraph(2), // Membuat deskripsi acak (2 paragraf)
                'price' => $faker->numberBetween(50000, 200000), // Membuat harga acak antara 50,000 dan 200,000
                'stock' => $faker->numberBetween(5, 100), // Membuat stok acak antara 5 dan 100
                'item_category_id' => $categoryIds[array_rand($categoryIds)], // Mengambil kategori acak
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Memasukkan 50 item ke dalam tabel
        DB::table('items')->insert($items);
    }
}