<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\SocialCounter;

class BangumiLikeCounter extends SocialCounter
{
    public function __construct()
    {
        parent::__construct('bangumi_like_counter', true);
    }
}
