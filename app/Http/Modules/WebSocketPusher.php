<?php


namespace App\Http\Modules;


use App\Http\Repositories\MessageRepository;
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

            $pusher->push($targetFd, json_encode([
                'channel' => 'unread_total',
                'unread_agree_count' => $user->unread_agree_count,
                'unread_reward_count' => $user->unread_reward_count,
                'unread_mark_count' => $user->unread_mark_count,
                'unread_comment_count' => $user->unread_comment_count,
                'unread_share_count' => $user->unread_share_count,
                'unread_message_count' => $user->unread_message_count
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
