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
        'count_view',
        'count_like',
        'count_mark',
        'count_reward',
        'count_comment',
        'count_share',
        'visit_state',  // 0：草稿，1：所有人可见，2：审核中仅自己可见
    ];
}
