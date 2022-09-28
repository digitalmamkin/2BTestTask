<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //
    public function getList(Request $request){
        $filter = json_decode($request->input('filter'));

        $posts = Post::offset($filter->length)->limit(10);

        // Blog ID;
        if(isset($filter->blog) && $filter->blog != 'all'){
            $posts = $posts->where('blog_id', $filter->blog);
        }

        // Read time;
        if(isset($filter->time) && $filter->time != 'all'){
            $posts = $posts->where('read_time', $filter->time);
        }

        // Dates;
        if(isset($filter->date_from) && isset($filter->date_to) && $filter->date_from != '' && $filter->date_to != ''){
            $posts = $posts->whereBetween('post_date', [$filter->date_from, $filter->date_to]);
        }

        $posts = $posts->orderBy('post_date', 'DESC')
            ->get();

        return [
            'status' => 200,
            'result' => $posts
        ];
    }

    public function getTimeList(){
        $times = Post::groupBy('read_time')
            ->select('read_time as time')
            ->get();

        return [
            'status' => 200,
            'result' => $times
        ];
    }
}
