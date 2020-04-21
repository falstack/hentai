<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\CacheStatCounter;
use App\Models\Message;

class UnreadMessageCounter extends CacheStatCounter
{
    public function __construct()
    {
        parent::__construct('unread_message', 'unread_message');
    }

    protected function computeTotal($table, $slug)
    {
        return Message
            ::where('getter_slug', $slug)
            ->where('read', '0')
            ->count();
    }
}
