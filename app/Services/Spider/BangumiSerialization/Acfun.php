<?php
/**
 * file description
 *
 * @version
 * @author daryl
 * @date 2020-05-25
 * @since 2020-05-25 description
 */

namespace App\Services\Spider\BangumiSerialization;


use Carbon\Carbon;
use Carbon\CarbonInterface;
use GuzzleHttp\Client;

class Acfun extends Serialization
{
    public function fetch()
    {
        $url = 'https://api-new.app.acfun.cn/rest/app/new-bangumi/schedule';

        $client = new Client();
        $response = $client->get($url);
        $data = json_decode($response->getBody()->getContents(), true);

        foreach ($data['bangumis'] as $bangumi) {
            $this->bangumis[] = [
                'site' => 2,
                'status' => 1,
                'url' => $bangumi['shareUrl'],
                'title' => $bangumi['title'],
                'current' => $bangumi['lastUpdateItemName'],
                'raw_id' => $bangumi['id'],
                'broadcast_time' => Carbon::today('Asia/Shanghai')
                    ->startOfWeek()
                    ->addDays($bangumi['updateWeekDay'])
                    ->addSeconds($bangumi['updateDayTime'] / 1000),
            ];
        }
    }
}