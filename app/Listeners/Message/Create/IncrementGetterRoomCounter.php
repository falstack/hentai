<?php


namespace App\Listeners\Message\Create;


use App\Http\Repositories\Repository;
use App\Models\MessageMenu;

class IncrementGetterRoomCounter
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Message\Create $event)
    {
        $message = $event->message;

        $getterMenuItem = MessageMenu::firstOrCreate([
            'sender_slug' => $message->sender_slug,
            'getter_slug' => $message->getter_slug,
            'type' => $message->type
        ]);

        $getterMenuItem->increment('count');

        $menuListCacheKey = MessageMenu::messageListCacheKey($message->getter_slug);
        $repository = new Repository();
        $repository->SortSet($menuListCacheKey, $event->roomId, $getterMenuItem->generateCacheScore());
    }
}
