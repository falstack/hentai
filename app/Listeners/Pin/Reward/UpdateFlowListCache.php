<?php

namespace App\Listeners\Pin\Reward;

use App\Http\Repositories\BangumiRepository;
use App\Http\Repositories\FlowRepository;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
     * @param  \App\Events\Pin\Reward  $event
     * @return void
     */
    public function handle(\App\Events\Pin\Reward $event)
    {
        $pin = $event->pin;
        if (!$pin->published_at || !$pin->can_up)
        {
            return;
        }

        $bangumiRepository = new BangumiRepository();
        $bangumiRepository->update_pin($pin->bangumi_slug, $pin->slug);
        if ($pin->recommended_at)
        {
            $bangumiRepository->recommend_pin($pin->slug);
        }

        $flowRepository = new FlowRepository();
        $flowRepository->updatePin(
            $pin->slug,
            $pin->bangumi_slug,
            $pin->user_slug
        );

        $pin->update([
            'updated_at' => Carbon::now()
        ]);
    }
}
