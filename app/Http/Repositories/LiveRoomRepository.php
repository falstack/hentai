<?php


namespace App\Http\Repositories;


use App\Http\Transformers\Question\QuestionItemResource;
use App\Models\BangumiQuestion;
use App\Models\IdolVoice;

class LiveRoomRepository extends Repository
{
    public function allVoice($type, $slug)
    {
        $result = $this->RedisArray("live-room-voice-all:{$type}:{$slug}", function () use ($type, $slug)
        {
            $list = IdolVoice
                ::where('from_type', $type)
                ->when($slug, function ($query) use ($slug)
                {
                    return $query->where('from_slug', $slug);
                })
                ->get()
                ->toArray();

            $result = [];
            foreach ($list as $item)
            {
                $result[] = json_encode([
                    'id' => $item['id'],
                    'src' => $item['src'],
                    'meta' => $item['meta'],
                    'text' => $item['text'],
                    'from_type' => $item['from_type'],
                    'from_slug' => $item['from_slug']
                ]);
            }

            return $result;
        });

        $repository = $type == '0' ? new IdolRepository() : new UserRepository();

        return array_map(function ($item) use ($repository)
        {
            $res = json_decode($item);

            $user = $repository->item($res->from_slug);

            $res->reader = [
                'id' => $user->id,
                'slug' => $user->slug,
                'name' => $user->name ?? $user->nickname,
                'avatar' => $user->avatar
            ];

            $meta = json_decode($res->meta);
            $res->duration = $meta->duration;
            $res->meta = $meta;
            $res->alias = isset($user->alias) ? implode(',', $user->alias) : '';

            return $res;
        }, $result);
    }
}
