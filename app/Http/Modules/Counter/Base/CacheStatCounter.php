<?php


namespace App\Http\Modules\Counter\Base;

use App\Http\Repositories\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CacheStatCounter extends Repository
{
    protected $table;
    protected $model;
    protected $withToday;

    /**
     * 作为数据仓库的 middleware，应用于按天计数的场景，不需要回写数据库
     */
    public function __construct($table, $model, $today = false)
    {
        $this->table = $table;
        $this->model = $model;
        $this->withToday = $today;
    }

    public function total($id = 0)
    {
        return (int)$this->RedisItem($this->cacheKey($id), function () use ($id)
        {
            if (gettype($this->table) === 'array')
            {
                $result = 0;
                foreach ($this->table as $table)
                {
                    $result += $this->computeTotal($table, $id);
                }
                return $result;
            }
            return $this->computeTotal($this->table, $id);
        });
    }

    public function today($id = 0)
    {
        if (!$this->withToday)
        {
            return 0;
        }

        return (int)$this->RedisItem($this->cacheKey($id, true), function () use ($id)
        {
            if (gettype($this->table) === 'array')
            {
                $result = 0;
                foreach ($this->table as $table)
                {
                    $result += $this->computeToday($table, $id);
                }
                return $result;
            }
            return $this->computeToday($this->table, $id);
        });
    }

    public function add($id = 0, $num = 1)
    {
        $cacheKey = $this->cacheKey($id);
        if (Redis::EXISTS($cacheKey))
        {
            Redis::INCRBYFLOAT($cacheKey, $num);
        }

        $cacheKey = $this->cacheKey($id, true);
        if ($this->withToday && Redis::EXISTS($cacheKey))
        {
            Redis::INCRBYFLOAT($cacheKey, $num);
        }
    }

    protected function computeTotal($table, $id)
    {
        return DB
            ::table($table)
            ->whereNull('deleted_at')
            ->count();
    }

    protected function computeToday($table, $id)
    {
        return DB
            ::table($table)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', Carbon::now()->today())
            ->count();
    }

    protected function cacheKey($id, $today = false)
    {
        return 'total_' . $this->model . '_stats' . ':' . $id . ($today ? strtotime(date('Y-m-d', time())) : '');
    }
}
