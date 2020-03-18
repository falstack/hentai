<?php


namespace App\Listeners\Pin\Recover;


use App\Http\Repositories\BangumiRepository;
use App\Http\Repositories\FlowRepository;

class UpdatePinFlow
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Pin\Recover $event)
    {
        $bangumiRepository = new BangumiRepository();
        $flowRepository = new FlowRepository();

        if ($event->published)
        {
            $pin = $event->pin;
            $bangumiRepository->add_pin($pin->bangumi_slug, $pin->slug);
            $flowRepository->createPin(
                $pin->slug,
                $pin->bangumi_slug,
                $pin->user_slug,
                ['index' => true, 'bangumi' => true]
            );
        }
    }
}
