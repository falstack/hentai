<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveRoom extends Model
{
    use SoftDeletes;

    protected $table = 'live_chats';

    protected $fillable = [
        'author_id',
        'title',
        'desc',
        'content',
        'visit_state'
    ];
}
