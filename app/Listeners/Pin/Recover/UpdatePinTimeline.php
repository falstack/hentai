<?php

namespace App\Listeners\Pin\Recover;

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
     * @param  \App\Events\Pin\Recover  $event
     * @return void
     */
    public function handle(\App\Events\Pin\Recover $event)
    {
        $pin = $event->pin;
        $pin->timeline()->create([
            'event_type' => 8,
            'event_slug' => $pin->user_slug
        ]);
    }
}
