<?php


namespace App\Listeners\Pin\Create;


use App\Http\Repositories\BangumiRepository;
use App\Http\Repositories\UserRepository;

class AddPinToFlow
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Pin\Create $event)
    {
        if (!$event->pin->bangumi_slug || !$event->doPublish)
        {
            return;
        }

        $bangumiRepository = new BangumiRepository();
        $bangumiRepository->add_pin($event->pin->bangumi_slug, $event->pin->slug);

        $userRepository = new UserRepository();
        $userRepository->toggle_pin($event->pin->user_slug, $event->pin->slug);
    }
}
