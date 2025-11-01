<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fiction',
                'description' => 'Kategori buku fiksi, termasuk novel dan cerita imajinatif.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Non-fiction',
                'description' => 'Kategori buku non-fiksi, termasuk biografi, sejarah, dan self-help.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fantasy',
                'description' => 'Kategori buku fantasi, dengan cerita yang melibatkan unsur magis dan dunia imajinatif.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Self-Help',
                'description' => 'Kategori buku yang berfokus pada pengembangan diri, kebiasaan baik, dan motivasi.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Finance',
                'description' => 'Kategori buku yang berfokus pada keuangan pribadi dan pengelolaan uang.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('item_categories')->insert($categories);
    }
}