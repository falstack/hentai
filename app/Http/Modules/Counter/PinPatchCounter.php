<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\HashCounter;
use App\Models\Pin;
use App\Models\Search;

class PinPatchCounter extends HashCounter
{
    public function __construct()
    {
        parent::__construct('pins');
    }

    public function boot($slug)
    {
        $pin = Pin
            ::where('slug', $slug)
            ->first();

        if (is_null($pin))
        {
            return [
                'visit_count' => 0,
                'comment_count' => 0,
                'mark_count' => 0,
                'reward_count' => 0,
                'like_count' => 0
            ];
        }

        $pinLikeCounter = new PinLikeCounter();
        $pinMarkCounter = new PinMarkCounter();
        $pinRewardCounter = new PinRewardCounter();
        $pinId = $pin->id;

        return [
            'visit_count' => $pin->visit_count,
            'comment_count' => $pin->comments()->count(),
            'mark_count' => $pinMarkCounter->total($pinId),
            'reward_count' => $pinRewardCounter->score($pinId),
            'like_count' => $pinLikeCounter->score($pinId)
        ];
    }

    public function search($slug, $result)
    {
        Search
            ::where('slug', $slug)
            ->where('type', 2)
            ->update([
                'score' =>
                    $result['visit_count'] +
                    $result['comment_count'] +
                    $result['mark_count'] +
                    $result['reward_count'] +
                    $result['like_count']
            ]);
    }
}
