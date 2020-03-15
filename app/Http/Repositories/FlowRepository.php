<?php


namespace App\Http\Repositories;


use App\Models\Pin;
use Illuminate\Support\Facades\DB;

class FlowRepository extends Repository
{
    public static $pinHottestVisitKey = 'pin-hottest-visit';

    public static $from = ['index', 'bangumi', 'user'];

    public static $order = ['newest', 'activity', 'hottest'];

    public static $indexSlug = '';

    public function pinNewest($from, $slug, $take, $lastId, $isUp)
    {
        $ids = $this->RedisSort($this->flow_pin_cache_key($from, self::$order[0], $slug), function () use ($from, $slug)
        {
            return Pin
                ::whereNotNull('published_at')
                ->where('trial_type', 0)
                ->when($from === 'bangumi', function ($query) use ($slug)
                {
                    return $query->where('bangumi_slug', $slug);
                })
                ->when($from === 'user', function ($query) use ($slug)
                {
                    return $query->where('user_slug', $slug);
                })
                ->orderBy('published_at', 'DESC')
                ->pluck('published_at', 'slug');

        }, ['is_time' => true]);

        return $this->filterIdsByMaxId($ids, $lastId, $take, false, $isUp);
    }

    public function pinActivity($from, $slug, $take, $seenIds, $randId)
    {
        $ids = $this->pinActivityIds($from, $slug, $randId);

        return $this->filterIdsBySeenIds($ids, $seenIds, $take);
    }

    public function pinHottest($from, $slug, $take, $seenIds, $randId)
    {
        $this->SortAdd(
            self::$pinHottestVisitKey,
            $this->flow_pin_cache_key($from, self::$order[2], $slug, $randId)
        );

        $ids = $this->pinHottestIds($from, $slug, $randId);

        return $this->filterIdsBySeenIds($ids, $seenIds, $take);
    }

    public function pinActivityIds($from, $slug, $randId, $refresh = false)
    {
        return $this->RedisSort($this->flow_pin_cache_key($from, self::$order[1], $slug, $randId), function () use ($from, $slug, $randId)
        {
            return Pin
                ::whereNotNull('published_at')
                ->where('trial_type', 0)
                ->where('can_up', 1)
                ->where(DB::raw('id % 10'), $randId)
                ->when($from === 'bangumi', function ($query) use ($slug)
                {
                    return $query->where('bangumi_slug', $slug);
                })
                ->when($from === 'user', function ($query) use ($slug)
                {
                    return $query->where('user_slug', $slug);
                })
                ->orderBy('updated_at', 'DESC')
                ->pluck('updated_at', 'slug');

        }, ['refresh' => $refresh, 'is_time' => true]);
    }

    public function pinHottestIds($from, $slug, $randId, $refresh = false)
    {
        return $this->RedisSort($this->flow_pin_cache_key($from, self::$order[2], $slug, $randId), function () use ($from, $slug, $randId)
        {
            $pins = Pin
                ::whereNotNull('published_at')
                ->where('trial_type', 0)
                ->where('can_up', 1)
                ->where(DB::raw('id % 10'), $randId)
                ->when($from === 'bangumi', function ($query) use ($slug)
                {
                    return $query->where('bangumi_slug', $slug);
                })
                ->when($from === 'user', function ($query) use ($slug)
                {
                    return $query->where('user_slug', $slug);
                })
                ->select('slug', 'visit_count', 'comment_count', 'like_count', 'mark_count', 'reward_count', 'published_at')
                ->take(500)
                ->get()
                ->toArray();

            // https://segmentfault.com/a/1190000004253816
            $result = [];
            $i = 0.1;

            foreach ($pins as $pin)
            {
                $result[$pin['slug']] = (
                        log(($pin['visit_count'] + 1), 10) * 4 +
                        log(($pin['comment_count'] * 4 + 1), M_E) +
                        log(($pin['like_count'] * 2 + $pin['mark_count'] * 3 + $pin['reward_count'] * 10 + 1), 10)
                    ) / pow(((time() - strtotime($pin['published_at'])) + 1), $i);
            }

            return $result;
        }, ['refresh' => $refresh]);
    }

    public function createPin($pinSlug, $bangumiSlug, $userSlug, $onlyBangumi = false)
    {
        $slugs = $onlyBangumi ? [$bangumiSlug] : [self::$indexSlug, $bangumiSlug, $userSlug];
        foreach ($slugs as $i => $slug)
        {
            $this->SortAdd($this->flow_pin_cache_key(self::$from[$i], self::$order[0], $slug), $pinSlug);
        }

        $this->update_pin($pinSlug, $bangumiSlug, $userSlug);
    }

    public function updatePin($pinSlug, $bangumiSlug, $userSlug, $onlyBangumi = false)
    {
        $slugs = $onlyBangumi ? [$bangumiSlug] : [self::$indexSlug, $bangumiSlug, $userSlug];
        foreach ($slugs as $i => $slug)
        {
            $this->SortAdd($this->flow_pin_cache_key(self::$from[$i], self::$order[1], $slug), $pinSlug);
        }
    }

    public function deletePin($pinSlug, $bangumiSlug, $userSlug, $onlyBangumi = false)
    {
        $slugs = $onlyBangumi ? [$bangumiSlug] : [self::$indexSlug, $bangumiSlug, $userSlug];
        $randId = substr((string)slug2id($pinSlug), -1);
        foreach (self::$order[0] as $order)
        {
            foreach ($slugs as $i => $slug)
            {
                $id = $order === 'hottest' ? $randId : 0;
                $this->SortRemove(
                    $this->flow_pin_cache_key(
                        self::$from[$i],
                        $order,
                        $slug,
                        $id
                    ),
                    $pinSlug
                );
            }
        }
    }

    private function flow_pin_cache_key(string $from, string $order, string $slug, $id = 0)
    {
        return "flow-pin-{$order}:{$from}-{$slug}-{$id}";
    }
}
