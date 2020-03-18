<?php

namespace App\Listeners\Pin\Recover;

use App\Http\Repositories\PinRepository;

class RefreshCache
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
        $pinRepository = new PinRepository();
        $pinRepository->item($pin->slug, true);
    }
}
