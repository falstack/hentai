<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\SocialCounter;

class PinLikeCounter extends SocialCounter
{
    public function __construct()
    {
        parent::__construct('pin_like_counter', true);
    }
}
