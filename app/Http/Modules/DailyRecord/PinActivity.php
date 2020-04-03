<?php


namespace App\Http\Modules\DailyRecord;

use App\Http\Modules\DailyRecord\Base\DailyRecord;

class PinActivity extends DailyRecord
{
    public function __construct()
    {
        parent::__construct(5);
    }

    protected function hook($tagSlug, $score)
    {

    }
}
