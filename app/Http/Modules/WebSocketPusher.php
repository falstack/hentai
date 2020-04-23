<?php


namespace App\Http\Modules;


use App\Http\Modules\Counter\PinCommentLikeCounter;
use App\Http\Modules\Counter\PinLikeCounter;
use App\Http\Modules\Counter\PinRewardCounter;
use App\Http\Modules\Counter\UserFollowCounter;
use App\Http\Repositories\MessageRepository;
use App\Models\Comment;
use App\Models\Message;
use App\User;

class WebSocketPusher
{
    public function pushUnreadMessage($slug, $server = null, $fd = null)
    {
        try
        {
            if ($fd)
            {
                $targetFd = $fd;
            }
            else
            {
                $targetFd = app('swoole')
                    ->wsTable
                    ->get('uid:' . $slug);

                if (false === $targetFd)
                {
                    return;
                }
                $targetFd = $targetFd['value'];
            }

            $pusher = $server ?: app('swoole');
            $user = User::where('slug', $slug)->first();
            $id = $user->id;

            $pinCommentLikeCounter = new PinCommentLikeCounter();
            $pinLikeCounter = new PinLikeCounter();
            $pinRewardCounter = new PinRewardCounter();
            $userFollowCounter = new UserFollowCounter();

            $pusher->push($targetFd, json_encode([
                'channel' => 'unread_total',
                'unread_pin_like_count' => $pinCommentLikeCounter->unread($id),
                'unread_comment_like_count' =>  $pinLikeCounter->unread($id),
                'unread_reward_count' => $pinRewardCounter->unread($id),
                'unread_mark_count' => 0,
                'unread_share_count' => 0,
                'unread_follow_count' => $userFollowCounter->unread($id),
                'unread_comment_count' => Comment::where('to_user_slug', $slug)->where('read', 0)->count(),
                'unread_message_count' => Message::where('getter_slug', $slug)->where('read', 0)->count()
            ]));
        }
        catch (\Exception $e) {}
    }

    public function pushChatMessage($slug, $message)
    {
        try
        {
            $targetFd = app('swoole')
                ->wsTable
                ->get('uid:' . $slug);

            if (false === $targetFd)
            {
                return;
            }
            app('swoole')->push($targetFd['value'], json_encode($message));
        }
        catch (\Exception $e)
        {

        }
    }

    public function pushUserMessageList($slug)
    {
        try
        {
            $targetFd = app('swoole')
                ->wsTable
                ->get('uid:' . $slug);

            if (false === $targetFd)
            {
                return;
            }
            $messageRepository = new MessageRepository();
            $menu = $messageRepository->menu($slug);

            app('swoole')->push($targetFd['value'], json_encode([
                'channel' => 'message-menu',
                'data' => $menu
            ]));
        }
        catch (\Exception $e)
        {

        }
    }
}
