<?php


namespace App\Services\Spider;


use App\Models\Bangumi;
use App\Models\BangumiTag;
use App\Models\Idol;
use App\Models\Search;
use App\Services\Qiniu\Qshell;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class BangumiSource
{
    public function updateReleaseBangumi()
    {
        $query = new Query();
        $newIds = $query->getNewsBangumi();
        $bangumiSlugs = [];
        foreach ($newIds as $i => $list)
        {
            foreach ($list as $id)
            {
                $bangumi = Bangumi
                    ::where('source_id', $id)
                    ->first();

                if ($bangumi)
                {
                    $bangumiSlugs[] = $bangumi->slug;
                    $bangumi->update([
                        'update_week' => $i + 1
                    ]);
                    $this->getBangumiIdols($id, $bangumi->slug);
                    continue;
                }

                $info = $query->getBangumiDetail($id);
                if (!$info)
                {
                    Redis::SADD('load-bangumi-failed-ids', $id);
                    continue;
                }

                $bangumi = $this->importBangumi($info);
                if ($bangumi)
                {
                    $bangumi->update([
                        'update_week' => $i + 1
                    ]);
                    $bangumiSlugs[] = $bangumi->slug;
                }
            }
        }

        Bangumi
            ::whereNotIn('slug', $bangumiSlugs)
            ->update([
                'update_week' => 0
            ]);

        DB
            ::table('idols')
            ->whereNotIn('bangumi_slug', $bangumiSlugs)
            ->update([
                'is_newbie' => 0
            ]);

        DB
            ::table('idols')
            ->whereIn('bangumi_slug', $bangumiSlugs)
            ->update([
                'is_newbie' => 1
            ]);
    }

    public function loadHottestBangumi()
    {
        $page = Redis::GET('load-hottest-bangumi-page') ?: 1;
        $fetchFailed = false;
        if ($page > 200)
        {
            $pages = Redis::SMEMBERS('load-bangumi-failed-page');
            if (!$pages)
            {
                return;
            }
            $fetchFailed = true;
            $page = $pages[0];
        }

        $this->getHottestBangumi($page);

        if ($fetchFailed)
        {
            Redis::SREM('load-bangumi-failed-page', $page);
        }
        else
        {
            Redis::SET('load-hottest-bangumi-page', intval($page) + 1);
        }
    }

    public function retryFailedBangumiIdols()
    {
        $ids = Redis::SMEMBERS('load-bangumi-idol-failed');
        if (!$ids)
        {
            return;
        }
        $arr = explode('##', $ids[0]);

        $result = $this->getBangumiIdols($arr[0], $arr[1]);
        if ($result)
        {
            Redis::SREM('load-bangumi-idol-failed', $ids[0]);
        }
    }

    public function retryFailedIdolDetail()
    {
        $ids = Redis::SMEMBERS('load-idol-failed-ids');
        if (!$ids)
        {
            return;
        }
        $arr = explode('##', $ids[0]);

        $result = $this->loadIdolItem($arr[0], $arr[1]);
        if ($result)
        {
            Redis::SREM('load-idol-failed-ids', $ids[0]);
        }
    }

    public function importBangumi($source)
    {
        if (!$source || !$source['name'])
        {
            return null;
        }

        if ($source['id'])
        {
            $bangumi = Bangumi
                ::where('source_id', $source['id'])
                ->first();

            if ($bangumi)
            {
                return $bangumi;
            }
        }

        $QShell = new Qshell();
        $alias = implode('|', array_unique($source['alias']));
        $type = isset($source['type']) ? intval($source['type']) : 0;
        $bangumi = Bangumi
            ::create([
                'title' => $source['name'],
                'avatar' => preg_match('/calibur/', $source['avatar']) ? $source['avatar'] : $QShell->fetch($source['avatar']),
                'intro' => $source['intro'],
                'alias' => $alias,
                'type' => $type,
                'published_at' => $source['published_at'] ?? null,
                'source_id' => $source['id'] ?? 0
            ]);

        $bangumiSlug = id2slug($bangumi->id);
        $bangumi->update([
            'slug' => $bangumiSlug
        ]);

        if ($type === 0 && $source['id'])
        {
            $this->getBangumiIdols($source['id'], $bangumiSlug);
            $this->importBangumiTags($source['id'], $bangumiSlug);
        }

        Search::create([
            'type' => 4,
            'slug' => $bangumiSlug,
            'text' => $alias,
            'score' => 0
        ]);

        return $bangumi;
    }

    public function getBangumiIdols($sourceId, $bangumiSlug)
    {
        $query = new Query();
        $ids = $query->getBangumiIdols($sourceId);
        if (false === $ids)
        {
            Redis::SADD('load-bangumi-idol-failed', "{$sourceId}##{$bangumiSlug}");
            return false;
        }

        foreach ($ids as $id)
        {
            $this->loadIdolItem($id, $bangumiSlug);
        }

        return true;
    }

    public function moveBangumiIdol($slug, $sourceId)
    {
        if (!$sourceId || !$slug)
        {
            return;
        }

        $query = new Query();
        $ids = $query->getBangumiIdols($sourceId);
        if (empty($ids))
        {
            return;
        }

        foreach ($ids as $id)
        {
            $this->loadIdolItem($id, $slug);
            DB
                ::table('idols')
                ->where('source_id', $id)
                ->update([
                    'bangumi_slug' =>  $slug
                ]);
        }

        return;
    }

    public function importIdol($source, $bangumiSlug)
    {
        if (!$source['name'])
        {
            return '';
        }

        $has = Idol
            ::where('source_id', $source['id'])
            ->first();

        if ($has)
        {
            return $has->slug;
        }

        $QShell = new Qshell();
        $alias = implode('|', array_unique($source['alias']));
        $idol = Idol
            ::create([
                'title' => $source['name'],
                'avatar' => preg_match('/calibur/', $source['avatar']) ? $source['avatar'] : $QShell->fetch($source['avatar']),
                'intro' => $source['intro'],
                'source_id' => $source['id'] ?? 0,
                'stock_price' => 1,
                'alias' => $alias,
                'bangumi_slug' => $bangumiSlug
            ]);

        $slug = id2slug($idol->id);
        $idol->update([
            'slug' => $slug
        ]);

        Search::create([
            'type' => 5,
            'slug' => $slug,
            'text' => $alias,
            'score' => 0
        ]);

        return $slug;
    }

    public function importBangumiTags($sourceId, $bangumiSlug)
    {
        $query = new Query();
        $tags = $query->getBangumiTags($sourceId);

        if (empty($tags))
        {
            return;
        }

        foreach ($tags as $name)
        {
            $tag = BangumiTag::where('name', $name)->first();
            if (!$tag)
            {
                $tag = BangumiTag::create([
                    'name' => $name
                ]);

                $tag->update([
                    'slug' => id2slug($tag->id)
                ]);
            }

            $has = DB
                ::table('bangumi_tag_relations')
                ->where('bangumi_slug', $bangumiSlug)
                ->where('tag_slug', $tag->slug)
                ->count();

            if ($has)
            {
                continue;
            }

            DB
                ::table('bangumi_tag_relations')
                ->insert([
                    'bangumi_slug' => $bangumiSlug,
                    'tag_slug' => $tag->slug
                ]);
        }

        return;
    }

    protected function getHottestBangumi($page)
    {
        $query = new Query();
        $list = $query->getBangumiList($page);

        if (empty($list))
        {
            Redis::SADD('load-bangumi-failed-page', $page);
        }

        foreach ($list as $item)
        {
            if (!$item)
            {
                Redis::SADD('load-bangumi-failed-ids', $id);
                continue;
            }

            $this->importBangumi($item);
        }
    }

    protected function loadIdolItem($id, $bangumiSlug)
    {
        if (!$id)
        {
            return true;
        }

        $query = new Query();
        $idol = $query->getIdolDetail($id);
        if (!$idol)
        {
            Redis::SADD('load-idol-failed-ids', "{$id}##{$bangumiSlug}");
            return false;
        }

        $result = $this->importIdol($idol, $bangumiSlug);

        return !!$result;
    }
}
