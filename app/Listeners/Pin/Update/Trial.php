<?php

namespace App\Listeners\Pin\Update;

use App\Http\Modules\RichContentService;
use App\Http\Repositories\FlowRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class Trial implements ShouldQueue
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
     * @param  \App\Events\Pin\Update  $event
     * @return void
     */
    public function handle(\App\Events\Pin\Update $event)
    {
        if (!$event->doPublish || !$event->published)
        {
            return;
        }

        $richContentService = new RichContentService();
        $risk = $richContentService->detectContentRisk($event->arrContent);
        $pin = $event->pin;
        if ($risk['delete'])
        {
            $pin->update([
                'trial_type' => 2
            ]);

            $flowRepository = new FlowRepository();
            $flowRepository->deletePin(
                $pin->slug,
                $pin->bangumi_slug,
                $pin->user_slug,
                ['index' => true, 'bangumi' => true]
            );

            $pin->timeline()->create([
                'event_type' => 10,
                'event_slug' => $event->user->slug
            ]);
        }
        if ($risk['review'])
        {
            $pin->update([
                'trial_type' => 1
            ]);

            $pin->timeline()->create([
                'event_type' => 9,
                'event_slug' => $event->user->slug
            ]);
        }
    }
}
