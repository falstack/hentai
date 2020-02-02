<?php


namespace App\Http\Repositories;


use App\Http\Transformers\Bangumi\BangumiItemResource;
use App\Models\Bangumi;
use App\Models\BangumiQuestionRule;
use App\Models\Idol;
use App\Models\Pin;
use App\User;
use Illuminate\Support\Carbon;

class BangumiRepository extends Repository
{
    public $times = [
        'all', '3-day', '7-day', '30-day'
    ];

    public function item($slug, $refresh = false)
    {
        if (!$slug)
        {
            return null;
        }

        $result = $this->RedisItem("bangumi:{$slug}", function () use ($slug)
        {
            $idol = Bangumi
                ::where('slug', $slug)
                ->with('tags')
                ->first();

            if (is_null($idol))
            {
                return 'nil';
            }

            return new BangumiItemResource($idol);
        }, $refresh);

        if ($result === 'nil')
        {
            return null;
        }

        return $result;
    }

    public function idol_slugs($slug, $page, $take, $refresh = false)
    {
        $list = $this->RedisSort($this->bangumiIdolsCacheKey($slug), function () use ($slug)
        {
            return Idol
                ::where('bangumi_slug', $slug)
                ->orderBy('market_price', 'DESC')
                ->orderBy('stock_price', 'DESC')
                ->pluck('market_price', 'slug')
                ->toArray();

        }, ['force' => $refresh]);

        return $this->filterIdsByPage($list, $page, $take);
    }

    public function rank($page, $take, $refresh = false)
    {
        $list = $this->RedisSort('bangumi-rank-slug', function ()
        {
            return Bangumi
                ::where('score', '>', 0)
                ->where('type', 0)
                ->orderBy('score', 'DESC')
                ->orderBy('id', 'DESC')
                ->pluck('score', 'slug')
                ->take(250)
                ->toArray();

        }, ['force' => $refresh]);

        return $this->filterIdsByPage($list, $page, $take);
    }

    public function rule($slug, $refresh = false)
    {
        return $this->RedisItem("bangumi-join-rule:{$slug}", function () use ($slug)
        {
            return BangumiQuestionRule
                ::where('bangumi_slug', $slug)
                ->first();
        }, $refresh);
    }

    public function likeUsers($slug, $page, $take, $refresh = false)
    {
        $list = $this->RedisSort($this->bangumiLikerCacheKey($slug), function () use ($slug)
        {
            $bangumi = Bangumi::where('slug', $slug)->first();
            if (!$bangumi)
            {
                return [];
            }

            return $bangumi
                ->fans(User::class)
                ->withPivot('created_at')
                ->orderBy('created_at', 'DESC')
                ->pluck('followables.created_at', 'slug')
                ->toArray();

        }, ['force' => $refresh, 'is_time' => true]);

        return $this->filterIdsByPage($list, $page, $take);
    }

    public function pins($slug, $sort, $isUp, $specId, $time, $take)
    {
        if ($sort === 'hottest')
        {
            $ids = $this->hottest_pin($slug, $time);
            $idsObj = $this->filterIdsBySeenIds($ids, $specId, $take);
        }
        else if ($sort === 'active')
        {
            $ids = $this->active_pin($slug);
            $idsObj = $this->filterIdsBySeenIds($ids, $specId, $take);
        }
        else
        {
            $ids = $this->newest_pin($slug);
            $idsObj = $this->filterIdsByMaxId($ids, $specId, $take, false, $isUp);
        }

        return $idsObj;
    }

    public function indexPin($seenIds, $take, $refresh = false)
    {
        $ids =  $this->RedisSort($this->index_pin_cache_key(), function ()
        {
            return Pin
                ::where('trial_type', 0)
                ->where('can_up', 1)
                ->where('content_type', 1)
                ->whereNotNull('published_at')
                ->whereNotIn('bangumi_slug', [
                    config('app.tag.default_daily'),
                    config('app.tag.default_news')
                ])
                ->select('slug', 'updated_at')
                ->orderBy('updated_at', 'DESC')
                ->pluck('updated_at', 'slug')
                ->toArray();

        }, ['force' => $refresh, 'is_time' => true]);

        return $this->filterIdsBySeenIds($ids, $seenIds, $take);
    }

    public function hottest_pin($slug, $time, $refresh = false)
    {
        return $this->RedisSort($this->hottest_pin_cache_key($slug, $time), function () use ($slug, $time)
        {
            $pins = Pin
                ::where('bangumi_slug', $slug)
                ->when($time !== 'all', function ($query) use ($time)
                {
                    if ($time === '3-day')
                    {
                        $date = Carbon::now()->addDays(-3);
                    }
                    else if ($time === '7-day')
                    {
                        $date = Carbon::now()->addDays(-7);
                    }
                    else if ($time === '30-day')
                    {
                        $date = Carbon::now()->addDays(-30);
                    }
                    else
                    {
                        $date = Carbon::now()->addDays(-1);
                    }
                    return $query->where('published_at', '>=', $date);
                })
                ->where('trial_type', 0)
                ->where('content_type', 1)
                ->where('can_up', 1)
                ->whereNotNull('published_at')
                ->select('slug', 'visit_count', 'comment_count', 'like_count', 'mark_count', 'reward_count', 'created_at')
                ->get()
                ->toArray();

            $result = [];
            if ($time === '3-day')
            {
                $i = 0.8;
            }
            else if ($time === '7-day')
            {
                $i = 0.5;
            }
            else if ($time === '30-day')
            {
                $i = 0.3;
            }
            else
            {
                $i = 0.1;
            }
            // https://segmentfault.com/a/1190000004253816
            foreach ($pins as $pin)
            {
                $result[$pin['slug']] = (
                    log(($pin['visit_count'] + 1), 10) * 4 +
                    log(($pin['comment_count'] * 4 + 1), M_E) +
                    log(($pin['like_count'] * 2 + $pin['mark_count'] * 3 + $pin['reward_count'] * 10 + 1), 10)
                ) / pow(((time() - strtotime($pin['created_at'])) + 1), $i);
            }

            return $result;
        }, ['force' => $refresh]);
    }

    public function newest_pin($slug, $refresh = false)
    {
        return $this->RedisSort($this->newest_pin_cache_key($slug), function () use ($slug)
        {
            return Pin
                ::where('bangumi_slug', $slug)
                ->where('can_up', 1)
                ->where('content_type', 1)
                ->whereNotNull('published_at')
                ->orderBy('published_at', 'DESC')
                ->pluck('published_at', 'slug');

        }, ['force' => $refresh, 'is_time' => true]);
    }

    public function active_pin($slug, $refresh = false)
    {
        return $this->RedisSort($this->active_pin_cache_key($slug), function () use ($slug)
        {
            return Pin
                ::where('bangumi_slug', $slug)
                ->where('can_up', 1)
                ->where('content_type', 1)
                ->whereNotNull('published_at')
                ->orderBy('updated_at', 'DESC')
                ->pluck('updated_at', 'slug');

        }, ['force' => $refresh, 'is_time' => true]);
    }

    public function add_pin($bangumiSlug, $pinSlug)
    {
        $this->SortAdd($this->newest_pin_cache_key($bangumiSlug), $pinSlug);
        $this->SortAdd($this->active_pin_cache_key($bangumiSlug), $pinSlug);
        $this->SortAdd($this->index_pin_cache_key(), $pinSlug);
    }

    public function update_pin($bangumiSlug, $pinSlug)
    {
        $this->SortAdd($this->newest_pin_cache_key($bangumiSlug), $pinSlug);
        $this->SortAdd($this->active_pin_cache_key($bangumiSlug), $pinSlug);
        $this->SortAdd($this->index_pin_cache_key(), $pinSlug);
    }

    public function del_pin($bangumiSlug, $pinSlug)
    {
        $this->SortRemove($this->newest_pin_cache_key($bangumiSlug), $pinSlug);
        $this->SortRemove($this->active_pin_cache_key($bangumiSlug), $pinSlug);
        $this->SortRemove($this->index_pin_cache_key(), $pinSlug);
        foreach ($this->times as $time)
        {
            $this->SortRemove($this->hottest_pin_cache_key($bangumiSlug, $time), $pinSlug);
        }
    }

    public function bangumiIdolsCacheKey($slug)
    {
        return "bangumi-{$slug}-idol-slug";
    }

    public function bangumiLikerCacheKey($slug)
    {
        return "bangumi-{$slug}-liker-slug";
    }

    protected function hottest_pin_cache_key(string $slug, $time)
    {
        return "bangumi-pin-hottest-{$slug}-{$time}";
    }

    protected function newest_pin_cache_key(string $slug)
    {
        return "bangumi-pin-newest-{$slug}-all";
    }

    protected function active_pin_cache_key(string $slug)
    {
        return "bangumi-pin-active-{$slug}-all";
    }

    protected function index_pin_cache_key()
    {
        return 'pin-index-ids';
    }
}
