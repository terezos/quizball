<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Premier League', 'icon' => '⚽', 'order' => 1],
            ['name' => 'Champions League', 'icon' => '🏆', 'order' => 2],
            ['name' => 'World Cup', 'icon' => '🌍', 'order' => 3],
            ['name' => 'Players & Legends', 'icon' => '👤', 'order' => 4],
            ['name' => 'Clubs & Stadiums', 'icon' => '🏟️', 'order' => 5],
            ['name' => 'Tactics & Rules', 'icon' => '📋', 'order' => 6],
            ['name' => 'Football History', 'icon' => '📚', 'order' => 7],
            ['name' => 'Transfers & Records', 'icon' => '💰', 'order' => 8],
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