<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Πρέμιερ Λιγκ', 'icon' => '⚽', 'order' => 1],
            ['name' => 'Τσάμπιονς Λιγκ', 'icon' => '🏆', 'order' => 2],
            ['name' => 'Παγκόσμιο Κύπελλο', 'icon' => '🌍', 'order' => 3],
            ['name' => 'Παίκτες & Θρύλοι', 'icon' => '👤', 'order' => 4],
            ['name' => 'Ομάδες & Γήπεδα', 'icon' => '🏟️', 'order' => 5],
            ['name' => 'Τακτικές & Κανόνες', 'icon' => '📋', 'order' => 6],
            ['name' => 'Ιστορία Ποδοσφαίρου', 'icon' => '📚', 'order' => 7],
            ['name' => 'Μεταγραφές & Ρεκόρ', 'icon' => '💰', 'order' => 8],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'icon' => $category['icon'],
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }
    }
}
