<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'title' => 'Laskar Pelangi',
                'description' => 'Novel inspiratif karya Andrea Hirata tentang perjuangan anak-anak Belitung.',
                'price' => 90000,
                'stock' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Bumi Manusia',
                'description' => 'Karya Pramoedya Ananta Toer, bagian dari Tetralogi Buru.',
                'price' => 120000,
                'stock' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Sapiens',
                'description' => 'Buku non-fiksi karya Yuval Noah Harari tentang sejarah umat manusia.',
                'price' => 150000,
                'stock' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Harry Potter dan Batu Bertuah',
                'description' => 'Novel fantasi karya J.K. Rowling, buku pertama seri Harry Potter.',
                'price' => 110000,
                'stock' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Atomic Habits',
                'description' => 'Buku self-help karya James Clear tentang membangun kebiasaan baik.',
                'price' => 130000,
                'stock' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Rich Dad Poor Dad',
                'description' => 'Buku keuangan pribadi karya Robert Kiyosaki.',
                'price' => 95000,
                'stock' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => '5 CM',
                'description' => 'Novel karya Donny Dhirgantoro tentang persahabatan dan petualangan mendaki Gunung Semeru.',
                'price' => 85000,
                'stock' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'The Alchemist',
                'description' => 'Novel Paulo Coelho tentang perjalanan seorang gembala muda mencari harta karun dan makna hidup.',
                'price' => 100000,
                'stock' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Negeri 5 Menara',
                'description' => 'Novel karya Ahmad Fuadi tentang persahabatan dan perjuangan di pondok pesantren.',
                'price' => 90000,
                'stock' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Think and Grow Rich',
                'description' => 'Buku klasik karya Napoleon Hill tentang mindset kesuksesan dan kekayaan.',
                'price' => 115000,
                'stock' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('items')->insert($books);
    }
}
