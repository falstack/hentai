<?php


namespace App\Http\Modules\DailyRecord;

use App\Http\Modules\DailyRecord\Base\DailyRecord;

class TagActivity extends DailyRecord
{
    public function __construct()
    {
        parent::__construct(3);
    }

    protected function hook($tagSlug, $score)
    {

    }
}
