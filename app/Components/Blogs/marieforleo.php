<?php

namespace App\Components\Blogs;

use App\Components\BlogParser;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use voku\helper\HtmlDomParser;

trait marieforleo
{
    static private ?int $blog_id = 5;
    static private ?string $log_channel = 'marieforleo';
    static private ?string $url = 'https://www.marieforleo.com';
    static private ?int $post_limit;

    public static function run($only_posts, $post_limit){
        static::$post_limit = $post_limit;
        Log::channel(static::$log_channel)->info('Scrape starting...');

        if(!$only_posts){
            $html = HtmlDomParser::str_get_html(file_get_contents(static::$url.'/blog'));

            // Scrape categories;
            $categories = $html->findMulti('.view-all');

            Log::channel(static::$log_channel)->info('Script got '.count($categories).' categories');

            $posts_counter = 0;
            foreach($categories as $category){
                $cat_url = $category->getAttribute('href');

                Log::channel(static::$log_channel)->info('Category URL: '.$cat_url);

                // Getting all posts;
                // Add small magic in the url for getting all posts in one page;
                $html = HtmlDomParser::str_get_html(file_get_contents($cat_url.'?page=10000'));
                $posts = $html->findMulti('a.blog-post-title-in-line-link-block');

                Log::channel(static::$log_channel)->info('Category have '.count($posts).' posts');

                foreach($posts as $post_item){
                    if(static::$post_limit > 0 && $posts_counter == static::$post_limit){
                        Log::channel(static::$log_channel)->info('Post limit researched...');
                        break 2;
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

                    $posts_counter++;
                }

                Log::channel(static::$log_channel)->info('Scrapping category successfully finished.');
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

            $title = $html->find('.blog-post-title', 0)->text();
            $content = $html->find('.blog-post-conatiner:nth-child(1)', 0)->innerhtml();
            $post_date = $html->find('div.collection-item-details-div-block > h5', 0)->text();

            $post->title = $title;
            $post->content = $content;
            $post->post_date = date('Y-m-d', strtotime($post_date));
            $post->read_time = BlogParser::calculateReadTime($content);
            $post->save();

            Log::channel(static::$log_channel)->info('Post is ready');
        }
    }
}
