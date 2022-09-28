<?php

namespace App\Components\Blogs;

use App\Components\BlogParser;
use App\Models\Post;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use voku\helper\HtmlDomParser;

trait cupofjo
{
    static private ?int $blog_id = 4;
    static private ?string $log_channel = 'cupofjo';
    static private ?string $url = 'https://cupofjo.com/';
    static private ?int $post_limit;

    public static function run($only_posts, $post_limit){
        static::$post_limit = $post_limit;
        Log::channel(static::$log_channel)->info('Scrape starting...');

        if(!$only_posts){
            // Scrape categories;
            $categories_urls = static::scrapeCategories();

            // Scrape posts in every category;
            foreach($categories_urls as $category_url){
                static::scrapePostsURLs($category_url);
            }
        }

        // Scrape "empty" posts;
        static::scrapePosts();

        Log::channel(static::$log_channel)->info('Scrape was finished...');

        return true;
    }

    // Scrape Categories;
    private static function scrapeCategories(){
        Log::channel(static::$log_channel)->info('Scrapping categories...');
        $html = HtmlDomParser::str_get_html(file_get_contents(static::$url));

        // Categories;
        $categories = $html->findMulti('.desktop-nav > div > li:not([id=""]):not(.about):not(.search-trigger) > a');

        $cat_urls = [];
        foreach($categories as $category){
            $url = $category->getAttribute('href');
            $cat_urls[] = $url;
            Log::channel(static::$log_channel)->info('Category URL: '.$url);
        }

        return $cat_urls;
    }

    // Scrape Posts URLs;
    private static function scrapePostsURLs($category_url){
        Log::channel(static::$log_channel)->info('Scrapping posts from: '.$category_url);

        $html_posts = HtmlDomParser::str_get_html(file_get_contents($category_url));

        // Getting category keyword;
        $key_word = $html_posts->find('link[rel="alternate"][type="application/json"]', 0)->getAttribute('href');
        $key_word = explode('/', $key_word);
        $key_word = Arr::last($key_word);

        Log::channel(static::$log_channel)->info('Category keyword: '.$key_word);

        // Posts;
        $posts = $html_posts->findMulti('article');
        Log::channel(static::$log_channel)->info('Post count on first page: '.count($posts));
        $posts_ids = static::postsConveyor($posts);

        // Scrape other posts from ajax pages;
        $ajax_handler = true;
        $temp_key = 2;
        while($ajax_handler){
            $next_posts_html = BlogParser::sendCurlPost(static::$url."wp-admin/admin-ajax.php", [
                'ids' => implode(',', $posts_ids),
                'new_home' => "false",
                'key_word' => $key_word,
                'is_cat' => "true",
                'is_tag' => "false",
                'is_auth' => "false",
                'is_offset' => "0",
                'action' => "more_post_ajax"
            ]);

            if(static::$post_limit > 0 && count($posts_ids) >= static::$post_limit){
                Log::channel(static::$log_channel)->info('Post limit researched...');
                $ajax_handler = false;
            }

            $html_posts = HtmlDomParser::str_get_html($next_posts_html);
            $posts = $html_posts->findMulti('article');
            Log::channel(static::$log_channel)->info('Posts count from '.$temp_key.' page: '.count($posts));
            $new_posts_ids = static::postsConveyor($posts);
            $posts_ids = array_merge($posts_ids, $new_posts_ids);

            $temp_key++;
            if(count($new_posts_ids) == 0){
                $ajax_handler = false;
            }
        }
    }

    // Small conveyor;
    private static function postsConveyor($posts){
        $posts_ids = [];
        foreach($posts as $post_item){
            $posts_ids[] = $post_item->getAttribute('id');
            $url = $post_item->find('a', 0)->getAttribute('href');

            // Checking on exist post URL;
            $exist_post = Post::where('blog_id', static::$blog_id)
                ->where('url', $url)
                ->first();

            if($exist_post == null){
                Log::channel(static::$log_channel)->info('New post added: '.$url);
                // Create post template for next works;
                $post = new Post();
                $post->blog_id = static::$blog_id;
                $post->url = $url;
                $post->save();
            }   else{
                Log::channel(static::$log_channel)->info('Exist posts was skipped: '.$url);
            }
        }

        return $posts_ids;
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

            $title = $html->find('.article-name', 0)->text();
            $content = $html->find('.article-body', 0)->innerhtml();
            $post_date = $html->find('.entry-date', 0)->text();

            $post->title = $title;
            $post->content = $content;
            $post->post_date = date('Y-m-d', strtotime($post_date));
            $post->read_time = BlogParser::calculateReadTime($content);
            $post->save();

            Log::channel(static::$log_channel)->info('Post is ready');
        }
    }


}
