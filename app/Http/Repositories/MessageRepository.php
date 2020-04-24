<?php
/**
 * Created by PhpStorm.
 * User: yuistack
 * Date: 2019-04-15
 * Time: 08:31
 */

namespace App\Http\Repositories;


use App\Http\Modules\RichContentService;
use App\Http\Transformers\Message\MessageItemResource;
use App\Models\Message;
use App\Models\MessageMenu;
use Illuminate\Support\Facades\DB;

class MessageRepository extends Repository
{
    public function history($type, $getterSlug, $senderSlug, $maxId, $isUp, $count)
    {
        $cacheKey = Message::roomCacheKey($type, $getterSlug, $senderSlug);
        $cache = $this->RedisSort($cacheKey, function () use ($type, $getterSlug, $senderSlug)
        {
            $messages = Message
                ::where('type', $type)
                ->whereRaw('getter_slug = ? and sender_slug = ?', [$senderSlug, $getterSlug])
                ->orWhereRaw('getter_slug = ? and sender_slug = ?', [$getterSlug, $senderSlug])
                ->with(['content'])
                ->get();

            if (empty($messages))
            {
                return [];
            }

            $messages = MessageItemResource::collection($messages);
            $result = [];
            foreach ($messages as $msg)
            {
                $result[json_encode($msg)] = $msg->id;
            }

            return $result;
        }, ['with_score' => true, 'desc' => false]);

        if (empty($cache))
        {
            return [
                'total' => 0,
                'result' => [],
                'no_more' => true
            ];
        }

        $format = $this->filterIdsByMaxId(array_flip($cache), $maxId, $count, true, $isUp);
        $userRepository = new UserRepository();
        $result = [];
        foreach ($format['result'] as $id => $item)
        {
            $message = json_decode($item, true);
            $message['sender'] = $userRepository->item($message['sender_slug']);
            $result[] = $message;
        }

        $format['result'] = $result;

        return $format;
    }

    public function newest($type, $getterSlug, $senderSlug)
    {
        $arr = $this->history($type, $getterSlug, $senderSlug, '', true, 1);
        if (empty($arr['result']))
        {
            return '';
        }

        $richContentService = new RichContentService();
        $item = $arr['result'][0];
        $text = $richContentService->paresPureContent($item['content']);

        return mb_substr($text, 0, 30, 'utf-8');
    }

    public function menu($slug)
    {
        $cacheKey = MessageMenu::messageListCacheKey($slug);
        $cache = $this->RedisSort($cacheKey, function () use ($slug)
        {
            $menus = MessageMenu
                ::where('getter_slug', $slug)
                ->orderBy('updated_at', 'DESC')
                ->get();

            $result = [];
            foreach ($menus as $menu)
            {
                $channel = Message::roomCacheKey($menu['type'], $menu['getter_slug'], $menu['sender_slug']);
                $result[$channel] = $menu->generateCacheScore();
            }

            return $result;
        }, ['with_score' => true]);

        if (empty($cache))
        {
            return [];
        }

        $result = [];
        $userRepository = new UserRepository();
        foreach ($cache as $channel => $score)
        {
            $arr = explode('@', $channel);
            $item = [
                'channel' => $channel,
                'time' => substr($score, 0, -3),
                'count' => intval(substr($score, -3)),
                'type' => $arr[1]
            ];
            if ($arr[1] == 1)
            {
                $item['about_user'] = $userRepository->item($arr[2] == $slug ? $arr[3] : $arr[2]);
                $item['desc'] = $this->newest($arr[1], $arr[2], $arr[3]);
            }
            $result[] = $item;
        }

        return $result;
    }

    public function agreeList($userId, $page, $take)
    {
        $cache = $this->RedisSort($this->agreeListCacheKey($userId), function () use ($userId)
        {
            $arr = DB::SELECT("
                SELECT *
                FROM(
                    SELECT `model_id` AS `id`, `user_id`, `created_at`, 'comment' as type
                    FROM `pin_comment_like_counter`
                    WHERE `author_id`= ?
                    union all
                    SELECT `model_id` AS `id`, `user_id`, `created_at`, 'pin' as type
                    FROM `pin_like_counter`
                    WHERE `author_id`= ?
                ) as total
            ", [$userId, $userId]);

            $result = [];
            foreach ($arr as $row)
            {
                $key = $row->type . ':' . $row->user_id . ':' . $row->id;
                $result[$key] = $row->created_at;
            }

            return $result;
        }, ['is_time' => true]);

        $cache = $this->filterIdsByPage($cache, $page, $take);
        $result = [];

        foreach ($cache['result'] as $row)
        {
            $arr = explode(':', $row);
            $result[] = [
                'id' => $arr[2],
                'type' => $arr[0],
                'user_id' => $arr[1],
            ];
        }
        $cache['result'] = $result;

        return $cache;
    }

    public function agreeListCacheKey($id)
    {
        return "agree_list_cache_{$id}";
    }
}
