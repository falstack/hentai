<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\SocialCounter;

class UserFollowCounter extends SocialCounter
{
    public function __construct()
    {
        parent::__construct('user_follow_counter', true, true);
    }
}
