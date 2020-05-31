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

use App\Models\BangumiSerialization;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\CarbonTimeZone;
use GuzzleHttp\Client;

class Bilibili extends Serialization
{
    public function fetch()
    {
        $client = new Client;

        $response = $client->get('https://bangumi.bilibili.com/web_api/timeline_global');

        $data = json_decode($response->getBody()->getContents(), true);

        foreach ($data['result'] as $key => $val) {
            $data[$val['date_ts']] = $val['seasons'];
            unset($data['result'][$key]);
        }

        foreach (Carbon::now("Asia/Shanghai")->startOfWeek()->daysUntil(Carbon::now('Asia/Shanghai')->endOfWeek()) as $day) {
            foreach ($data[$day->timestamp] as $bangumi) {
                $this->bangumis[] = [
                    'site' => 1,
                    'status' => 0 == $bangumi['delay'] ? 1 : 2,
                    'url' => $bangumi['url'],
                    'title' => $bangumi['title'],
                    'current' => 0 == $bangumi['delay'] ? $bangumi['pub_index'] : $bangumi['delay_index'],
                    'raw_id' => $bangumi['season_id'],
                    'broadcast_time' => Carbon::createFromTimestamp($bangumi['pub_ts']),
                ];
            }
        }
    }
}
