<?php


namespace App\Http\Modules\Spider;


use App\Http\Modules\Spider\Base\GetResourceService;
use Carbon\Carbon;

class BilBiliResourceSpider extends GetResourceService
{
    /**
     * model_type
     * 1. 视频
     */
    public function __construct()
    {
        parent::__construct(1);
    }

    public function getDataItem($id, $type, $source)
    {
        $data = $this->getClient("https://api.bilibili.com/x/web-interface/view/detail?bvid={$id}");
        $row = $data['data']['View'];
        if ($source === null)
        {
            return [
                'username' => $row['owner']['name'],
                'poster' => $this->getImage($row['pic']),
                'title' => $row['title'],
                'visit_count' => $row['stat']['view'],
                'comment_count' => $row['stat']['reply'],
                'danmu_count' => $row['stat']['danmaku'],
                'published_at' => $row['pubdate'],
                'duration' => $row['duration']
            ];
        }

        return array_merge($source, [
            'visit_count' => $row['stat']['view'],
            'comment_count' => $row['stat']['reply'],
            'danmu_count' => $row['stat']['danmaku']
        ]);
    }

    public function getUserList($id, $rule)
    {
        $data = $this->getClient("https://api.bilibili.com/x/space/arc/search?mid={$id}&pn=1&ps=100");
        $list = $data['data']['list']['vlist'];

        $result = [];
        $now = Carbon::now();
        foreach ($list as $row)
        {
            $result[] = [
                'source_type' => 0,
                'site_type' => 1,
                'model_type' => 1,
                'model_id' => $row['bvid'],
                'author_id' => $row['mid'],
                'data' => json_encode([
                    'username' => $row['author'],
                    'poster' => $this->getImage($row['pic']),
                    'title' => $row['title'],
                    'visit_count' => $row['play'],
                    'comment_count' => $row['comment'],
                    'danmu_count' => 0,
                    'published_at' => $row['created'],
                    'duration' => $this->formatDurationStr($row['length'])
                ]),
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        return $result;
    }

    protected function formatDurationStr($str)
    {
        $arr = explode(':', $str);
        if (count($arr) === 1)
        {
            return intval($arr[0]);
        }

        if (count($arr) === 2)
        {
            return 60 * intval($arr[0]) + intval($arr[1]);
        }

        if (count($arr) === 3)
        {
            return 3600 * intval($arr[0]) + 60 * intval($arr[1]) + intval($arr[2]);
        }

        return 0;
    }
}
