<?php

namespace App\Listeners\Comment\Create;

use App\Http\Repositories\BangumiRepository;
use App\Http\Repositories\FlowRepository;
use App\Models\Pin;
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
     * @param  \App\Events\Comment\Create  $event
     * @return void
     */
    public function handle(\App\Events\Comment\Create $event)
    {
        $comment = $event->comment;
        $slug = $comment->pin_slug;
        $pin = Pin
            ::where('slug', $slug)
            ->first();

        if (!$pin)
        {
            return;
        }

        if (!$pin->published_at || !$pin->can_up || $pin->trial_type != 0)
        {
            return;
        }

        if ($pin->user_slug == $comment->from_user_slug && !$comment->to_user_slug)
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
