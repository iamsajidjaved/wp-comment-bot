<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentSubmission extends Model
{
    protected $fillable = [
        'post_url',
        'comment_id',
        'author',
        'email',
        'comment',
        'status',
    ];
}
