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
        $getter = User::where('slug', $event->message->getter_slug)->first();
        if (!$getter)
        {
            return;
        }

        $getter->updateMsgCount('message');
    }
}
