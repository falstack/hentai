<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\SocialCounter;

class PinCommentLikeCounter extends SocialCounter
{
    public function __construct()
    {
        parent::__construct('pin_comment_like_counter', true);
    }
}
