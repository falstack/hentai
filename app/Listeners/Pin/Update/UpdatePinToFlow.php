<?php


namespace App\Listeners\Pin\Update;


use App\Http\Repositories\BangumiRepository;

class UpdatePinToFlow
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Pin\Update $event)
    {
        $bangumiRepository = new BangumiRepository();

        if (!$event->doPublish)
        {
            if ($event->oldBangumiSlug)
            {
                $bangumiRepository->del_pin($event->oldBangumiSlug, $event->pin->slug);
            }
        }

        if ($event->published)
        {
            if ($event->newBangumiSlug)
            {
                $bangumiRepository->add_pin($event->newBangumiSlug, $event->pin->slug);
            }
        }
    }
}
