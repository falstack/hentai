<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\SocialCounter;

class PinRewardCounter extends SocialCounter
{
    public function __construct()
    {
        parent::__construct('pin_reward_counter', true);
    }
}
