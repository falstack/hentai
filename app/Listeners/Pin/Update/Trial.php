<?php

namespace App\Listeners\Pin\Update;

use App\Http\Modules\RichContentService;
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
        if ($risk['delete'])
        {
            $event->pin->deletePin($event->user);
        }
        if ($risk['review'])
        {
            $event->pin->update([
                'trial_type' => 1
            ]);
        }
    }
}
