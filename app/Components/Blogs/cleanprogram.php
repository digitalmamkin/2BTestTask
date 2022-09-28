<?php

namespace App\Components\Blogs;

use App\Components\BlogParser;
use App\Models\Post;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use voku\helper\HtmlDomParser;

trait cleanprogram
{
    static private ?int $blog_id = 2;
    static private ?string $log_channel = 'cleanprogram';
    static private ?string $url = 'https://www.cleanprogram.com';
    static private ?int $post_limit;

    public static function run($only_posts, $post_limit){
        static::$post_limit = $post_limit;
        Log::channel(static::$log_channel)->info('Scrape starting...');

        if(!$only_posts){
            $html = HtmlDomParser::str_get_html(file_get_contents(static::$url.'/blogs/clean'));

            // Pages in the blog;
            $pages = $html->findMulti('.page > a');

            // Last page;
            $last_page_url = $pages[count($pages)-1]->getAttribute('href');
            $last_page = explode('/', $last_page_url);
            $last_page = str_replace('clean?page=', '', Arr::last($last_page));

            for($i = 1 ; $i <= $last_page ; $i++){
                Log::channel(static::$log_channel)->info('Scrapping page: '.$i);

                $html = HtmlDomParser::str_get_html(file_get_contents(static::$url.'/blogs/clean?page='.$i));

                // Posts on the page;
                $posts = $html->findMulti('.post > a');
                foreach($posts as $key => $post_item){
                    if(static::$post_limit > 0 && $key + 1 == static::$post_limit){
                        Log::channel(static::$log_channel)->info('Post limit researched...');
                        break;
                    }

                    $url = static::$url.$post_item->getAttribute('href');

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
            if(static::$post_limit > 0 && $key + 1 == static::$post_limit){
                Log::channel(static::$log_channel)->info('Post limit researched...');
                break;
            }

            Log::channel(static::$log_channel)->info('Researching post: '.$post->url);

            $html = HtmlDomParser::str_get_html(file_get_contents($post->url));

            $title = $html->find('meta[property="og:title"]', 0)->getAttribute('content');
            $content = $html->find('article.post-body', 0)->innerhtml();

            $post->title = $title;
            $post->content = $content;
            $post->post_date = date('Y-m-d');
            $post->read_time = BlogParser::calculateReadTime($content);
            $post->save();

            Log::channel(static::$log_channel)->info('Post is ready');
        }
    }
}
