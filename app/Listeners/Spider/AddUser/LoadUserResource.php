<?php


namespace App\Listeners\Spider\AddUser;

use App\Http\Modules\Spider\BilBiliResourceSpider;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoadUserResource implements ShouldQueue
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Spider\AddUser $event)
    {
        $siteType = $event->siteType;
        if ($siteType !== 1)
        {
            return;
        }

        $service = new BilBiliResourceSpider();
        $service->getNewestResources($event->userId);
    }
}
