<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Blog;
use App\Components\BlogParser;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('parse {blog_id} {only_posts} {post_limit}', function () {
    $blog_id = $this->argument('blog_id');
    $post_limit = (int)$this->argument('post_limit');
    $only_posts = $this->argument('only_posts') == 'true';

    $blog = Blog::find($blog_id);

    if($blog == null){
        $this->error('ERROR: Unknown blog ID. Check DB or seeder for getting actual blog ID...');
        return;
    }

    $this->info('Trying to parse "'.$blog->title.'"...');
    $result = BlogParser::run($blog->id, $only_posts, $post_limit);

    if($result === true){
        $this->info('Parsing for "'.$blog->title.'" successfully finished!');
    }   else{
        $this->error($result);
    }
})->purpose("Using for parsing posts by blog ID.\r\nblog_id (int) - Blog ID for scrapping.\r\nonly_posts (bool) - Scrapping only uncompleted posts.\r\npost_limit (int) - Post limit for scrapping, 0 - mean all.");
