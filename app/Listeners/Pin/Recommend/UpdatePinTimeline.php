<?php

namespace App\Listeners\Pin\Recommend;

use App\Events\Pin\Recommend;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePinTimeline
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
     * @param  \App\Events\Pin\Recommend  $event
     * @return void
     */
    public function handle(\App\Events\Pin\Recommend $event)
    {
        $event->pin->timeline()->create([
            'event_type' => $event->result ? 6 : 7,
            'event_slug' => $event->user->slug
        ]);
    }
}
