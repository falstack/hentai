<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\CacheStatCounter;
use App\Models\Message;

class UnreadNoticeCounter extends CacheStatCounter
{
    public function __construct()
    {
        parent::__construct('unread_notice', 'unread_notice');
    }

    protected function computeTotal($table, $slug)
    {
        return Message
            ::where('getter_slug', $slug)
            ->where('read', '0')
            ->count();
    }
}
