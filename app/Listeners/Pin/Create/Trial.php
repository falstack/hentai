<?php


namespace App\Listeners\Pin\Create;


use App\Http\Modules\RichContentService;
use App\Http\Repositories\BangumiRepository;
use App\Http\Repositories\FlowRepository;
use App\Http\Repositories\UserRepository;

class Trial
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Pin\Create $event)
    {
        if (!$event->doPublish)
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
