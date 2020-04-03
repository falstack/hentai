<?php


namespace App\Http\Modules\DailyRecord;


use App\Http\Modules\DailyRecord\Base\DailyRecord;

class TagExposure extends DailyRecord
{
    public function __construct()
    {
        parent::__construct(4);
    }

    protected function hook($tagSlug, $score)
    {

    }
}
