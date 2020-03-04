<?php


namespace App\Http\Modules\Counter;


use App\Models\Search;
use App\Models\Tag;

class TagPatchCounter extends HashCounter
{
    public function __construct()
    {
        parent::__construct('tags');
    }

    public function boot($slug)
    {
        return [
            'pin_count' => 0,
            'seen_user_count' => 0,
            'followers_count' => 0,
            'question_count' => 0,
            'activity_stat' => 0
        ];
    }

    public function search($slug, $result)
    {
        Search
            ::where('slug', $slug)
            ->where('type', 1)
            ->update([
                'score' =>
                    $result['pin_count'] +
                    $result['seen_user_count'] +
                    $result['followers_count'] +
                    $result['activity_stat']
            ]);
    }
}
