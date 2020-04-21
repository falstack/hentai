<?php

namespace App\Listeners\Pin\UpVote;

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
     * @param  \App\Events\Pin\UpVote  $event
     * @return void
     */
    public function handle(\App\Events\Pin\UpVote $event)
    {
        $pin = $event->pin;
        if (!$pin->published_at || !$pin->can_up || $pin->trial_type != 0 || !$event->result)
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
