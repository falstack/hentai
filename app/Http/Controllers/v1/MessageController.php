<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Modules\Counter\PinCommentLikeCounter;
use App\Http\Modules\Counter\PinLikeCounter;
use App\Http\Modules\Counter\PinRewardCounter;
use App\Http\Modules\Counter\UserFollowCounter;
use App\Http\Repositories\CommentRepository;
use App\Http\Repositories\MessageRepository;
use App\Http\Repositories\PinRepository;
use App\Http\Repositories\Repository;
use App\Http\Repositories\UserRepository;
use App\Models\Comment;
use App\Models\Message;
use App\Models\MessageMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function getMessageTotal(Request $request)
    {
        $user = $request->user();
        $slug = $user->slug;
        $id = $user->id;

        $pinCommentLikeCounter = new PinCommentLikeCounter();
        $pinLikeCounter = new PinLikeCounter();
        $pinRewardCounter = new PinRewardCounter();
        $userFollowCounter = new UserFollowCounter();

        return $this->resOK([
            'channel' => 'unread_total',
            'unread_pin_like_count' => $pinCommentLikeCounter->unread($id),
            'unread_comment_like_count' =>  $pinLikeCounter->unread($id),
            'unread_reward_count' => $pinRewardCounter->unread($id),
            'unread_mark_count' => 0,
            'unread_share_count' => 0,
            'unread_follow_count' => $userFollowCounter->unread($id),
            'unread_comment_count' => Comment::where('to_user_slug', $slug)->where('read', 0)->count(),
            'unread_message_count' => Message::where('getter_slug', $slug)->where('read', 0)->count()
        ]);
    }

    public function messageOfComment(Request $request)
    {
        $lastId = $request->get('last_id') ?: 0;
        $count = $request->get('take') ?: 20;
        $user = $request->user();
        $slug = $user->slug;

        $list = Comment
            ::where('to_user_slug', $slug)
            ->when($lastId != 0, function ($query) use ($lastId)
            {
                return $query->where('id', '>', $lastId);
            })
            ->orderBy('read', 'ASC')
            ->orderBy('id', 'DESC')
            ->pluck('pin_slug', 'id')
            ->take($count)
            ->toArray();

        $pinRepository = new PinRepository();
        $commentRepository = new CommentRepository();

        $result = [];

        foreach ($list as $commentId => $pinSlug)
        {
            $result[] = [
                'id' => $commentId,
                'comment' => $commentRepository->item($commentId),
                'pin' => $pinRepository->item($pinSlug)
            ];
        }

        return $this->resOK([
            'result' => $result
        ]);
    }

    public function messageOfAgree(Request $request)
    {
        $page = $request->get('page') ?: 1;
        $take = $request->get('take') ?: 20;
        $user = $request->user();
        $userId = $user->id;

        $messageRepository = new MessageRepository();

        $resObj = $messageRepository->agreeList($userId, $page - 1, $take);
        if (empty($resObj['result']))
        {
            return $this->resOK($resObj);
        }

        $result = [];
        $commentRepository = new CommentRepository();
        $userRepository = new UserRepository();
        $pinRepository = new PinRepository();

        foreach ($resObj['result'] as $row)
        {
            $type = $row['type'];

            if ($type === 'comment')
            {
                $data = $commentRepository->item($row->id);
            }
            else if ($type === 'pin')
            {
                $data = $pinRepository->item($row->id);
            }

            $result[] = [
                'type' => $type,
                'data' => $data,
                'user' => $userRepository->item($row->user_id)
            ];
        }

        $resObj['result'] = $result;

        return $this->resOK($resObj);
    }

    public function messageOfReward(Request $request)
    {
        $lastId = $request->get('last_id') ?: 0;
        $count = $request->get('take') ?: 20;
        $user = $request->user();

        $pinRewardCounter = new PinRewardCounter();
        $message = $pinRewardCounter->message($user->id, $lastId, $count);
        $pinRepository = new PinRepository();
        $userRepository = new UserRepository();

        foreach ($message as $i => $row)
        {
            $message[$i]->user = $userRepository->item($row->user_id);
            $message[$i]->pin = $pinRepository->item($row->model_id);
        }

        return $this->resOK([
            'result' => $message
        ]);
    }

    public function messageOfFollow(Request $request)
    {
        $lastId = $request->get('last_id') ?: 0;
        $count = $request->get('take') ?: 20;
        $user = $request->user();

        $userFollowCounter = new UserFollowCounter();
        $message = $userFollowCounter->message($user->id, $lastId, $count);
        $userRepository = new UserRepository();

        foreach ($message as $i => $row)
        {
            $message[$i]->user = $userRepository->item($row->user_id);
        }

        return $this->resOK([
            'result' => $message
        ]);
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channel' => 'required|string',
            'content' => 'required|array'
        ]);

        if ($validator->fails())
        {
            return $this->resErrParams($validator);
        }

        $sender = $request->user();
        $senderSlug = $sender->slug;
        $channel = explode('@', $request->get('channel'));
        if (count($channel) < 4)
        {
            return $this->resErrBad();
        }

        $messageType = $channel[1];
        $getterSlug = $channel[2];
        if ($getterSlug === $senderSlug)
        {
            $getterSlug = $channel[3];
        }

        if ($messageType === 1 && $senderSlug === $getterSlug)
        {
            return $this->resErrBad();
        }

        $message = Message::createMessage([
            'sender_slug' => $senderSlug,
            'getter_slug' => $getterSlug,
            'type' => $messageType,
            'content' => $request->get('content'),
            'sender' => $sender
        ]);

        if (is_null($message))
        {
            return $this->resErrBad();
        }

        return $this->resCreated($message);
    }

    public function getMessageMenu(Request $request)
    {
        $user = $request->user();
        $slug = $user->slug;

        $messageRepository = new MessageRepository();
        $cache = $messageRepository->menu($slug);
        if (empty($cache))
        {
            return $this->resOK([
                'result' => [],
                'no_more' => true,
                'total' => 0
            ]);
        }

        return $this->resOK([
            'total' => 0,
            'result' => $cache,
            'no_more' => true
        ]);
    }

    public function getChatHistory(Request $request)
    {
        $channel = explode('@', $request->get('channel'));
        if (count($channel) < 4)
        {
            return $this->resErrBad();
        }
        $user = $request->user();

        $messageType = $channel[1];
        $getterSlug = $channel[2];
        $senderSlug = $user->slug;
        if ($getterSlug === $senderSlug)
        {
            $getterSlug = $channel[3];
        }
        $lastId = intval($request->get('last_id'));
        $isUp = (boolean)$request->get('is_up') ?: false;
        $count = $request->get('count') ?: 15;

        $messageRepository = new MessageRepository();
        $result = $messageRepository->history($messageType, $getterSlug, $senderSlug, $lastId, $isUp, $count);

        return $this->resOK($result);
    }

    public function getMessageChannel(Request $request)
    {
        $user = $request->user();
        $type = $request->get('type');
        $senderSlug = $request->get('slug');
        $getterSlug = $user->slug;
        if ($senderSlug === $getterSlug)
        {
            return $this->resErrBad('不能给自己发私信');
        }

        $menu = MessageMenu
            ::firstOrCreate([
                'sender_slug' => $senderSlug,
                'getter_slug' => $getterSlug,
                'type' => $type
            ]);

        $channel = Message::roomCacheKey($type, $getterSlug, $senderSlug);

        /**
         * 如果之前没聊过天，那么缓存里就没有这个 roomId，要加上
         */
        $cacheKey = MessageMenu::messageListCacheKey($getterSlug);
        $repository = new Repository();
        $repository->SortSet($cacheKey, $channel, $menu->generateCacheScore(0));

        return $this->resOK($channel);
    }

    public function deleteMessageChannel(Request $request)
    {
        $channel = explode('@', $request->get('channel'));
        if (count($channel) < 4)
        {
            return $this->resErrBad();
        }

        $user = $request->user();
        $messageType = $channel[1];
        $senderSlug = $channel[2];
        $getterSlug = $user->slug;
        if ($senderSlug === $getterSlug)
        {
            $senderSlug = $channel[3];
        }

        MessageMenu
            ::where('type', $messageType)
            ->where('sender_slug', $senderSlug)
            ->where('getter_slug', $getterSlug)
            ->delete();

        /**
         * 删掉自己列表的缓存
         */
        $cacheKey = MessageMenu::messageListCacheKey($getterSlug);
        $roomId = Message::roomCacheKey($messageType, $getterSlug, $senderSlug);
        $repository = new Repository();
        $repository->SortRemove($cacheKey, $roomId);

        return $this->resNoContent();
    }

    public function clearMessageChannel(Request $request)
    {
        $channel = explode('@', $request->get('channel'));
        if (count($channel) < 4)
        {
            return $this->resErrBad();
        }

        $user = $request->user();
        $messageType = $channel[1];
        $senderSlug = $channel[2];
        $getterSlug = $user->slug;
        if ($senderSlug === $getterSlug)
        {
            $senderSlug = $channel[3];
        }

        $menu = MessageMenu
            ::where('type', $messageType)
            ->where('getter_slug', $getterSlug)
            ->where('sender_slug', $senderSlug)
            ->first();

        if (is_null($menu))
        {
            return $this->resErrNotFound();
        }

        $count = $menu->count;
        if (!$count)
        {
            return $this->resNoContent();
        }

        Message
            ::where('getter_slug', $getterSlug)
            ->where('sender_slug', $senderSlug)
            ->update([
                'read' => 1
            ]);

        $menu->update([
            'count' => 0
        ]);

        $cacheKey = MessageMenu::messageListCacheKey($getterSlug);
        $roomId = Message::roomCacheKey($messageType, $getterSlug, $senderSlug);
        $repository = new Repository();
        $repository->SortSet($cacheKey, $roomId, $menu->generateCacheScore(0));

        return $this->resNoContent();
    }
}
