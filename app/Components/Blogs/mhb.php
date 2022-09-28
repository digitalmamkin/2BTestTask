<?php

namespace App\Components\Blogs;

use App\Components\BlogParser;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use voku\helper\HtmlDomParser;

trait mhb
{
    static private ?int $blog_id = 3;
    static private ?string $log_channel = 'mhb';
    static private ?string $url = 'https://mhb.xyz/essays/';
    static private ?int $post_limit;

    public static function run($only_posts, $post_limit){
        static::$post_limit = $post_limit;
        Log::channel(static::$log_channel)->info('Scrape starting...');

        if(!$only_posts){
            $html = HtmlDomParser::str_get_html(file_get_contents(static::$url));

            $posts = $html->findMulti('article > a');
            foreach($posts as $post){
                $url = $post->getAttribute('href');

                $exist_post = Post::where('blog_id', static::$blog_id)
                    ->where('url', $url)
                    ->first();

                if($exist_post == null){
                    $post = new Post();
                    $post->blog_id = static::$blog_id;
                    $post->url = $url;
                    $post->save();

                    Log::channel(static::$log_channel)->info('New post added: '.$url);
                }   else{
                    Log::channel(static::$log_channel)->info('Exist posts was skipped: '.$url);
                }
            }
        }

        // Scrape "empty" posts;
        static::scrapePosts();

        Log::channel(static::$log_channel)->info('Scrape was finished...');

        return true;
    }

    // Scrape Posts data from URLs;
    private static function scrapePosts(){
        $posts = Post::where('blog_id', static::$blog_id)
            ->whereNull('title')
            ->whereNull('content')
            ->get();

        foreach($posts as $key => $post){
            if(static::$post_limit > 0 && $key + 1 == static::$post_limit){
                Log::channel(static::$log_channel)->info('Post limit researched...');
                break;
            }

            Log::channel(static::$log_channel)->info('Researching post: '.$post->url);

            $html = HtmlDomParser::str_get_html(file_get_contents($post->url));

            $title = $html->find('.entry-header > h1', 0)->text();
            $content = $html->find('.entry-content', 0)->innerhtml();
            $post_date = $html->find('time.entry-date', 0)->text();

            $post->title = $title;
            $post->content = $content;
            $post->post_date = date('Y-m-d', strtotime($post_date));
            $post->read_time = BlogParser::calculateReadTime($content);
            $post->save();

            Log::channel(static::$log_channel)->info('Post is ready');
        }
    }
}
