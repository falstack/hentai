<?php


namespace App\Listeners\Pin\Move;


use App\Http\Repositories\BangumiRepository;

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
    }
}
