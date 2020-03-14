<?php


namespace App\Listeners\Pin\Move;


use App\Http\Repositories\BangumiRepository;
use App\Http\Repositories\FlowRepository;

class UpdatePinFlow
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Pin\Move $event)
    {
        $bangumiRepository = new BangumiRepository();
        $bangumiRepository->del_pin($event->oldBangumiSlug, $event->pin->slug);
        $bangumiRepository->add_pin($event->newBangumiSlug, $event->pin->slug);

        $flowRepository = new FlowRepository();
        $flowRepository->deletePin(
            $event->pin->slug,
            $event->oldBangumiSlug,
            $event->pin->user_slug,
            true
        );
        $flowRepository->updatePin(
            $event->pin->slug,
            $event->newBangumiSlug,
            $event->pin->user_slug,
            true
        );
    }
}
