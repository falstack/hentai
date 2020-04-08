<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\SocialCounter;

class PinMarkCounter extends SocialCounter
{
    public function __construct()
    {
        parent::__construct('pin_mark_counter', true);
    }
}
