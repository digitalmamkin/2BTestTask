<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        // Posts;
        DB::table('posts')->truncate();
        // Blogs;
        DB::table('blogs')->truncate();
        $this->call(Blogs::class);

        Schema::enableForeignKeyConstraints();
    }
}
