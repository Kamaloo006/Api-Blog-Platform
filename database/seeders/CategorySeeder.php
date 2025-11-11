<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            (object)[
                "name" => "Sports"
            ],
            (object)[
                "name" => "Jobs"
            ],
            (object)[
                "name" => "Healthcare"
            ],
            (object)[
                "name" => "Art"
            ],
            (object)[
                "name" => "Finance"
            ],
            (object)[
                "name" => "Music"
            ],
            (object)[
                "name" => "UFC"
            ],
            (object)[
                "name" => "GYM"
            ],
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category->name]);
        }
    }
}
