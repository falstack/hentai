<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\SocialCounter;

class PinShareCounter extends SocialCounter
{
    public function __construct()
    {
        parent::__construct('pin_share_counter', true);
    }
}
