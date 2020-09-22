<?php


namespace App\Http\Repositories;


use App\Http\Transformers\Question\QuestionItemResource;
use App\Models\BangumiQuestion;
use App\Models\IdolVoice;

class LiveRoomRepository extends Repository
{
    public function allVoice()
    {
        $result = $this->RedisArray('live-room-voice-all', function ()
        {
            $list = IdolVoice
                ::get()
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

        $idolRepository = new IdolRepository();
        $userRepository = new UserRepository();

        return array_map(function ($item) use ($idolRepository, $userRepository)
        {
            $res = json_decode($item, true);

            $fromType = $res['from_type'];

            if ($fromType == '0')
            {
                $user = $idolRepository->item($res['from_slug']);
            }
            else if ($fromType == '1')
            {
                $user = $userRepository->item($res['from_slug']);
            }

            $res['reader'] = [
                'id' => $user['id'],
                'slug' => $user['slug'],
                'name' => $user['name'] ?? $user['nickname'],
                'avatar' => $user['avatar']
            ];

            $meta = json_decode($res['meta'], true);
            $res['duration'] = $meta['duration'];
            $res['source_id'] = $res['id'];

            return $res;
        }, $result);
    }
}
