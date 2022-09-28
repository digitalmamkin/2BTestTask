<?php

namespace Database\Seeders;

use App\Models\Blog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Blogs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Blog::insert([
            [
                'id' => 1,
                'title' => 'adamenfroy',
                'url' => 'https://www.adamenfroy.com/blog',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'title' => 'cleanprogram',
                'url' => 'https://www.cleanprogram.com/blogs/clean/',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'title' => 'mhb',
                'url' => 'https://mhb.xyz/essays/',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'title' => 'cupofjo',
                'url' => 'https://cupofjo.com/',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'title' => 'marieforleo',
                'url' => 'https://www.marieforleo.com/blog',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
