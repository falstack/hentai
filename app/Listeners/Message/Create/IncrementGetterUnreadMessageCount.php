<?php


namespace App\Listeners\Message\Create;


use App\User;

class IncrementGetterUnreadMessageCount
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Message\Create $event)
    {
        User
            ::where('slug', $event->message->getter_slug)
            ->increment('unread_message_count');
    }
}
