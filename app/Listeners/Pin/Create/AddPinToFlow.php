<?php


namespace App\Listeners\Pin\Create;


use App\Http\Repositories\BangumiRepository;

class AddPinToFlow
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Pin\Create $event)
    {
        if (!$event->pin->bangumi_slug)
        {
            return;
        }

        $bangumiRepository = new BangumiRepository();
        $bangumiRepository->add_pin($event->pin->bangumi_slug, $event->pin->slug);
    }
}
