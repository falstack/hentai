<?php


namespace App\Listeners\Bangumi\Pass;


use App\Http\Modules\Counter\BangumiLikeCounter;
use App\Http\Repositories\UserRepository;

class AppendUserBangumiList
{
    public function __construct()
    {

    }

    public function handle(\App\Events\Bangumi\Pass $event)
    {
        $bangumiLikeCounter = new BangumiLikeCounter();
        $bangumiLikeCounter->set($event->user->id, $event);
        $userRepository = new UserRepository();
        $userRepository->SortAdd(
            $userRepository->userLikeBanguiCacheKey($event->user->slug),
            $event->bangumi->slug
        );
    }
}
