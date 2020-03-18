<?php

namespace App\Listeners\Pin\Recover;

use App\Http\Repositories\UserRepository;

class UpdateUserTimeline
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
        $event->user->timeline()->create([
            'event_type' => 5,
            'event_slug' => $event->pin->slug
        ]);
    }
}
