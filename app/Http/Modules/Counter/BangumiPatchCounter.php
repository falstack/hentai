<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\HashCounter;
use App\Models\Bangumi;
use App\Models\Pin;
use App\Models\Search;

class BangumiPatchCounter extends HashCounter
{
    public function __construct()
    {
        parent::__construct('bangumis');
    }

    public function boot($slug)
    {
        $bangumi = Bangumi
            ::where('slug', $slug)
            ->first();

        if (is_null($bangumi))
        {
            return [
                'publish_pin_count' => 0,
                'subscribe_user_count' => 0,
                'like_user_count' => 0
            ];
        }

        $bangumiId = $bangumi->id;
        $bangumiLikeCounter = new BangumiLikeCounter();

        return [
            'publish_pin_count' => Pin
                ::where('bangumi_slug', $slug)
                ->whereNotNull('published_at')
                ->count(),
            'subscribe_user_count' => 0, // TODO
            'like_user_count' => $bangumiLikeCounter->score($bangumiId)
        ];
    }

    public function search($slug, $result)
    {
        Search
            ::where('slug', $slug)
            ->where('type', 1)
            ->update([
                'score' =>
                    $result['publish_pin_count'] +
                    $result['subscribe_user_count'] +
                    $result['like_user_count']
            ]);
    }
}
