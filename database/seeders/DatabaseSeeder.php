<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User
        \App\Models\User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => bcrypt('password'),
            'access_key' => Str::random(40),
        ]);

        // Buku nyata
        $books = [
            [
                'title' => 'Clean Code',
                'description' => 'A Handbook of Agile Software Craftsmanship',
                'price' => 250000,
                'isbn' => '9780132350884',
                'author' => 'Robert C. Martin',
                'publisher' => 'Prentice Hall',
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/41xShlnTZTL._SX374_BO1,204,203,200_.jpg',
                'stock' => 20, // ✅ Tambah stok
            ],
            [
                'title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'description' => 'Classic design patterns book',
                'price' => 300000,
                'isbn' => '9780201633610',
                'author' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'publisher' => 'Addison-Wesley',
                'cover_image' => 'https://m.media-amazon.com/images/I/51kuc0iWo6L._SX376_BO1,204,203,200_.jpg',
                'stock' => 15, // ✅ Tambah stok
            ],
            [
                'title' => 'Refactoring: Improving the Design of Existing Code',
                'description' => 'Martin Fowler\'s refactoring guide',
                'price' => 280000,
                'isbn' => '9780201485677',
                'author' => 'Martin Fowler',
                'publisher' => 'Addison-Wesley',
                'cover_image' => 'https://m.media-amazon.com/images/I/41jEbK-jG+L._SX396_BO1,204,203,200_.jpg',
                'stock' => 10, // ✅ Tambah stok
            ],
        ];

        foreach ($books as $book) {
            \App\Models\Product::create($book);
        }

        // Dummy random products (pake factory, stok otomatis keisi)
        \App\Models\Product::factory(10)->create();
    }
}
