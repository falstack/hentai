<?php


namespace App\Http\Modules\Counter\Base;


use Illuminate\Support\Facades\DB;

class SocialCounter
{
    protected $table;
    protected $ab;          // 是否是 1/-1

    public function __construct($tabName, $isAB = false)
    {
        $this->table = $tabName;
        $this->ab = $isAB;
    }

    /**
     * 创建、更新数据
     */
    public function set($userId, $modelId, $authorId, $value = 1)
    {
        $data = null;

        if ($this->ab)
        {
            $data = DB
                ::table($this->table)
                ->where('user_id', $userId)
                ->where('model_id', $modelId)
                ->first();
        }

        if (is_null($data))
        {
            DB
                ::table($this->table)
                ->create([
                    'user_id' => $userId,
                    'model_id' => $modelId,
                    'author_id' => $authorId,
                    'value' => $value
                ]);

            return;
        }

        DB
            ::table($this->table)
            ->where('id', $data->id)
            ->update([
                'value' => $value
            ]);
    }

    /**
     * 删除数据
     */
    public function del($userId, $modelId)
    {
        DB
            ::table($this->table)
            ->where('user_id', $userId)
            ->where('model_id', $modelId)
            ->delete();
    }

    /**
     * 获取数据
     */
    public function get($userId, $modelId)
    {
        $value = DB
            ::table($this->table)
            ->where('user_id', $userId)
            ->where('model_id', $modelId)
            ->pluck('value')
            ->first();

        return $value === null ? 0 : intval($value);
    }

    /**
     * 获得该模型的所有「正向」用户
     */
    public function users($modelId, $withScore = false)
    {
        $data = DB
            ::table($this->table)
            ->where('model_id', $modelId)
            ->where('value', '>', 0)
            ->pluck('value', 'user_id')
            ->toArray();

        $result = [];
        if ($withScore)
        {
            foreach ($data as $key => $val)
            {
                $result[$key] = (int)$val;
            }
        }
        else
        {
            foreach ($data as $key => $val)
            {
                $result[] = $key;
            }
        }

        return $result;
    }

    /**
     * 获取该模型「正向」用户的个数
     */
    public function total($modelId)
    {
        return DB
            ::table($this->table)
            ->where('model_id', $modelId)
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
            ->where('model_id', $modelId)
            ->sum('value');
    }

    /**
     * 获取作者的所有未读消息
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
     * 一个用户获取多个关联关系
     */
    public function batch($userId, $modelIds)
    {
        $data = DB
            ::table($this->table)
            ->where('user_id', $userId)
            ->whereIn('model_id', $modelIds)
            ->pluck('value', 'model_id')
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
}
