<?php


namespace App\Listeners\Pin\Create;


use App\Http\Modules\RichContentService;
use App\Http\Repositories\FlowRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class Trial implements ShouldQueue
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
                'event_type' => 8,
                'event_slug' => 'create-2'
            ]);
        }
        if ($risk['review'])
        {
            $pin->update([
                'trial_type' => 1
            ]);

            $pin->timeline()->create([
                'event_type' => 8,
                'event_slug' => 'create-1'
            ]);
        }
    }
}
