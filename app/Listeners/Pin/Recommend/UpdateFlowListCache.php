<?php

namespace App\Listeners\Pin\Recommend;

use App\Http\Repositories\BangumiRepository;

class UpdateFlowListCache
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\Pin\Move  $event
     * @return void
     */
    public function handle(\App\Events\Pin\Recommend $event)
    {
        $bangumiRepository = new BangumiRepository();
        $bangumiRepository->recommend_pin($event->pin->slug, $event->result);
    }
}
