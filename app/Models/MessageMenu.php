<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class MessageMenu extends Model
{
    protected $fillable = [
        'sender_slug',      // 触发消息的用户slug
        'getter_slug',      // 接受消息的用户slug
        'count',            // 未读消息的条数
        'type',             // 消息的类型
    ];

    protected $casts = [
        'count' => 'integer',
    ];

    public function generateCacheScore($number = null)
    {
        $count = $number === null ? $this->count : $number;
        if (intval($count) > 999)
        {
            $msgCount = '999';
        }
        else
        {
            $msgCount = str_pad($count, 3, '0', STR_PAD_LEFT);
        }
        return ($number === null ? strtotime($this->updated_at) : time()) . $msgCount;
    }

    public static function messageListCacheKey($slug)
    {
        return "user-msg-menu:{$slug}";
    }
}
