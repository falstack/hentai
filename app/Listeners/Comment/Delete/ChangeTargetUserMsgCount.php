<?php


namespace App\Listeners\Comment\Delete;


use App\Http\Modules\Counter\PinPatchCounter;
use App\User;

class ChangeTargetUserMsgCount
{
    /**
     * Change the event listener.
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
     * @param  \App\Events\Comment\Delete  $event
     * @return void
     */
    public function handle(\App\Events\Comment\Delete $event)
    {
        $slug = $event->comment->to_user_slug;
        if (!$slug)
        {
            return;
        }

        $target = User::where('slug', $slug)->first();
        if (!$target)
        {
            return;
        }

        $target->updateMsgCount('comment', -1);
    }
}
