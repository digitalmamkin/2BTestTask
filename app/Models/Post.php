<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int|mixed $blog_id
 * @property mixed|string $url
 */
class Post extends Model
{
    use HasFactory;
}
