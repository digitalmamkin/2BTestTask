<?php

namespace App\Components\Blogs;

use App\Components\BlogParser;
use App\Models\Post;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use voku\helper\HtmlDomParser;

trait adamenfroy
{
    static private ?int $blog_id = 1;
    static private ?string $log_channel = 'adamenfroy';
    static private ?string $url = 'https://www.adamenfroy.com/blog';
    static private ?int $post_limit;

    public static function run($only_posts, $post_limit){
        static::$post_limit = $post_limit;
        Log::channel(static::$log_channel)->info('Scrape starting...');

        if(!$only_posts){
            $html = HtmlDomParser::str_get_html(file_get_contents(static::$url));

            // Pages in the blog;
            $pages = $html->findMulti('.page-numbers');

            // Last page;
            $last_page_url = $pages[count($pages)-2]->getAttribute('href');
            $last_page = explode('/', $last_page_url);
            $last_page = Arr::last($last_page);

            $post_counter = 0;
            for($i = 1 ; $i <= $last_page ; $i++){
                Log::channel(static::$log_channel)->info('Scrapping page: '.$i);

                $html = HtmlDomParser::str_get_html(file_get_contents(static::$url.'/page/'.$i));

                // Posts on the page;
                $posts = $html->findMulti('a[itemprop="mainEntityOfPage"]');
                foreach($posts as $post_item){
                    if(static::$post_limit > 0 && $post_counter == static::$post_limit){
                        Log::channel(static::$log_channel)->info('Post limit researched...');
                        break 2;
                    }

                    $url = $post_item->getAttribute('href');

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

                    $post_counter++;
                }

                Log::channel(static::$log_channel)->info('Page: '.$i.' successfully scraped.');
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
            if(static::$post_limit > 0 && $key == static::$post_limit){
                Log::channel(static::$log_channel)->info('Post limit researched...');
                break;
            }

            Log::channel(static::$log_channel)->info('Researching post: '.$post->url);

            $html = HtmlDomParser::str_get_html(file_get_contents($post->url));

            $title = $html->find('.page-title', 0)->text();
            $content = $html->find('.single-article-content', 0)->innerhtml();
            $post_date = $html->find('.last-updated-header', 0)->text();
            $post_date = str_replace('Updated ', '', $post_date);

            $post->title = $title;
            $post->content = $content;
            $post->post_date = date('Y-m-d', strtotime($post_date));
            $post->read_time = BlogParser::calculateReadTime($content);
            $post->save();

            Log::channel(static::$log_channel)->info('Post is ready');
        }
    }
}
