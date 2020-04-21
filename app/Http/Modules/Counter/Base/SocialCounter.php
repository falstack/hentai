<?php


namespace App\Http\Modules\Counter\Base;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SocialCounter
{
    protected $table;
    protected $isAB;        // 是否是 1/-1
    protected $isUser;      // 是否是用户关系
    protected $fieldName;

    public function __construct($tabName, $isAB = false, $isUser = false)
    {
        $this->table = $tabName;
        $this->isAB = $isAB;
        $this->isUser = $isUser;
        $this->fieldName = $isUser ? 'author_id' : 'model_id';
    }

    /**
     * 创建、更新数据
     */
    public function set($userId, $modelId, $authorId = 0, $value = 1, $time = 0)
    {
        $data = DB
            ::table($this->table)
            ->where('user_id', $userId)
            ->where('model_id', $modelId)
            ->where('author_id', $authorId)
            ->first();

        $time = $time ? $time : Carbon::now();

        if (is_null($data))
        {
            DB
                ::table($this->table)
                ->insert([
                    'user_id' => $userId,
                    'model_id' => $modelId,
                    'author_id' => $authorId,
                    'value' => $value,
                    'created_at' => $time,
                    'updated_at' => $time
                ]);

            return;
        }

        DB
            ::table($this->table)
            ->where('id', $data->id)
            ->update([
                'value' => $value,
                'updated_at' => $time
            ]);
    }

    public function toggle($userId, $modelId, $authorId = 0, $value = 1)
    {
        $data = DB
            ::table($this->table)
            ->where('user_id', $userId)
            ->where('model_id', $modelId)
            ->where('author_id', $authorId)
            ->first();

        $time = Carbon::now();
        if (is_null($data))
        {
            DB
                ::table($this->table)
                ->insert([
                    'user_id' => $userId,
                    'model_id' => $modelId,
                    'author_id' => $authorId,
                    'value' => $value,
                    'created_at' => $time,
                    'updated_at' => $time
                ]);

            return $value;
        }

        if ($this->isAB && $data->value != $value)
        {
            DB
                ::table($this->table)
                ->where('id', $data->id)
                ->update([
                    'value' => $value,
                    'updated_at' => $time
                ]);

            return $value;
        }

        DB
            ::table($this->table)
            ->where('id', $data->id)
            ->delete();

        return 0;
    }

    /**
     * 删除数据
     */
    public function del($userId, $modelId)
    {
        DB
            ::table($this->table)
            ->where($this->fieldName, $modelId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * 是否有过某行为
     */
    public function has($userId, $modelId)
    {
        if (!$userId)
        {
            return false;
        }

        $result = $this->get($userId, $modelId);

        return $result !== 0;
    }

    public function batchHas($data, $userId, $modelIds, $key)
    {
        foreach ($data as $i => $v)
        {
            $data[$i][$key] = false;
        }

        if (!$userId)
        {
            return $data;
        }

        $list = DB
            ::table($this->table)
            ->whereIn($this->fieldName, $modelIds)
            ->where('user_id', $userId)
            ->pluck('value', $this->fieldName)
            ->toArray();

        foreach ($list as $i => $v)
        {
            $data[$i][$key] = $v != 0;
        }

        return $data;
    }

    /**
     * 获取数据
     */
    public function get($userId, $modelId)
    {
        $value = DB
            ::table($this->table)
            ->where($this->fieldName, $modelId)
            ->where('user_id', $userId)
            ->pluck('value')
            ->first();

        return $value === null ? 0 : intval($value);
    }

    /**
     * 一个用户获取多个关联关系
     */
    public function batchGet($userId, $modelIds)
    {
        $data = DB
            ::table($this->table)
            ->where('user_id', $userId)
            ->whereIn($this->fieldName, $modelIds)
            ->pluck('value', $this->fieldName)
            ->toArray();

        $result = [];
        foreach ($modelIds as $id)
        {
            $result[(int)$id] = 0;
        }

        foreach ($data as $key => $val)
        {
            $result[(int)$key] = (int)$val;
        }

        return $result;
    }

    /**
     * 获得该模型的所有「正向」用户
     */
    public function users($modelId, $withScore = false)
    {
        $data = DB
            ::table($this->table)
            ->where($this->fieldName, $modelId)
            ->where('value', '>', 0)
            ->orderBy('updated_at', 'DESC')
            ->pluck('updated_at', 'user_id')
            ->toArray();

        if ($withScore)
        {
            return $data;
        }

        return array_keys($data);
    }

    public function list($userId, $withScore = false)
    {
        $data = DB
            ::table($this->table)
            ->where('user_id', $userId)
            ->where('value', '>', 0)
            ->orderBy('updated_at', 'DESC')
            ->pluck('updated_at', $this->fieldName)
            ->toArray();

        if ($withScore)
        {
            return $data;
        }

        return array_keys($data);
    }

    /**
     * 「我的粉丝」列表
     */
    public function fans($userId, $withScore = false)
    {
        return $this->users($userId, $withScore = false);
    }

    /**
     * 「我的关注」列表
     */
    public function focus($userId, $withScore = false)
    {
        return $this->list($userId, $withScore = false);
    }

    /**
     * 获取该模型「正向」用户的个数
     */
    public function total($modelId)
    {
        return DB
            ::table($this->table)
            ->where($this->fieldName, $modelId)
            ->where('value', '>', 0)
            ->count();
    }

    /**
     * 「我的关注」总数
     */
    public function following($userId)
    {
        return DB
            ::table($this->table)
            ->where('user_id', $userId)
            ->where('value', '>', 0)
            ->count();
    }

    /**
     * 「我的粉丝」总数
     */
    public function followers($userId)
    {
        return DB
            ::table($this->table)
            ->where('author_id', $userId)
            ->where('value', '>', 0)
            ->count();
    }

    /**
     * 获取该模型的分数
     */
    public function score($modelId)
    {
        return (int)DB
            ::table($this->table)
            ->where($this->fieldName, $modelId)
            ->sum('value');
    }

    /**
     * 获取作者的所有消息
     */
    public function message($authorId)
    {
        return DB
            ::table($this->table)
            ->where('author_id', $authorId)
            ->where('value', '>', 0)
            ->orderBy('updated_at', 'DESC')
            ->pluck('model_id')
            ->toArray();
    }

    /**
     * 获取作者的所有未读消息
     */
    public function unread($authorId)
    {
        return DB
            ::table($this->table)
            ->where('author_id', $authorId)
            ->where('value', '>', 0)
            ->where('read', 0)
            ->count();
    }
}
