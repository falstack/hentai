<?php


namespace App\Listeners\Pin\Update;


use App\Http\Repositories\BangumiRepository;
use App\Http\Repositories\FlowRepository;

class UpdatePinFlow
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Pin\Update $event)
    {
        $bangumiRepository = new BangumiRepository();
        $flowRepository = new FlowRepository();

        if (!$event->doPublish)
        {
            if ($event->oldBangumiSlug)
            {
                $bangumiRepository->del_pin($event->oldBangumiSlug, $event->pin->slug);
                $flowRepository->deletePin(
                    $event->pin->slug,
                    $event->oldBangumiSlug,
                    $event->pin->user_slug,
                    true
                );
            }
        }

        if ($event->published)
        {
            if ($event->newBangumiSlug)
            {
                $bangumiRepository->add_pin($event->newBangumiSlug, $event->pin->slug);
                $flowRepository->updatePin(
                    $event->pin->slug,
                    $event->newBangumiSlug,
                    $event->pin->user_slug,
                    true
                );
            }
        }
    }
}
