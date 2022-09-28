<?php

namespace App\Components;

use App\Components\Blogs\adamenfroy;
use App\Components\Blogs\cleanprogram;
use App\Components\Blogs\cupofjo;
use App\Components\Blogs\marieforleo;
use App\Components\Blogs\mhb;

trait BlogParser
{
    // Runner;
    static function run($blog_id, $only_posts, $post_limit){
        return match ($blog_id) {
            1 => adamenfroy::run($only_posts, $post_limit),
            2 => cleanprogram::run($only_posts, $post_limit),
            3 => mhb::run($only_posts, $post_limit),
            4 => cupofjo::run($only_posts, $post_limit),
            5 => marieforleo::run($only_posts, $post_limit),
            default => 'You are trying to parse unknown blog',
        };
    }

    // Curl POST request;
    public static function sendCurlPost($url, $post_form = []){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_form));
        $request_result = curl_exec($ch);
        curl_close($ch);

        return $request_result;
    }

    // Read time;
    public static function calculateReadTime( $content = '', $wpm = 250 ) {
        $clean_content = strip_tags( $content );
        $word_count = str_word_count( $clean_content );
        return ceil( $word_count / $wpm );
    }
}
