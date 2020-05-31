<?php
/**
 * file description
 *
 * @version
 * @author daryl
 * @date 2020-05-21
 * @since 2020-05-21 description
 */

namespace App\Services\Spider\BangumiSerialization;

use App\Models\Bangumi;
use App\Models\BangumiSerialization;
use Illuminate\Support\Facades\DB;

abstract class Serialization {
    abstract public function fetch();

    protected $bangumis = [];

    public function handle()
    {
        $this->fetch();
        $this->insertDb();
    }

    protected function insertDb()
    {
        foreach ($this->bangumis as $bangumi) {
            $possibleBangumi = Bangumi::where('title', $bangumi['title'])->first();
            $bangumi['bangumi_id'] = $possibleBangumi['id'] ?? 0;
            $bangumi['season_id'] = $possibleBangumi['id'] ?? 0;
            $serialization = BangumiSerialization::updateOrCreate(
                [
                    'site' => $bangumi['site'],
                    'raw_id' => $bangumi['raw_id'],
                ],
                $bangumi
            );

            if (!empty($possibleBangumi) && !empty($possibleBangumi['title']) && !empty($possibleBangumi['id']) && 0 == $possibleBangumi['serialization_id']) {
                $possibleBangumi['serialization_status'] = 1;
                $possibleBangumi['serialization_id'] = $serialization['id'];
                $possibleBangumi->save();
            }
        }
    }
}