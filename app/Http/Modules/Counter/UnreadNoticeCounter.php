<?php


namespace App\Http\Modules\Counter;


use App\Http\Modules\Counter\Base\CacheStatCounter;
use App\Models\Comment;

class UnreadNoticeCounter extends CacheStatCounter
{
    public function __construct()
    {
        parent::__construct('unread_notice', 'unread_notice');
    }

    protected function computeTotal($table, $slug)
    {
        $total = 0;
        $id = slug2id($slug);

        $pinCommentLikeCounter = new PinCommentLikeCounter();
        $total += $pinCommentLikeCounter->unread($id);

        $pinLikeCounter = new PinLikeCounter();
        $total += $pinLikeCounter->unread($id);

        $pinMarkCounter = new PinMarkCounter();
        $total += $pinMarkCounter->unread($id);

        $pinRewardCounter = new PinRewardCounter();
        $total += $pinRewardCounter->unread($id);

        $userFollowCounter = new UserFollowCounter();
        $total += $userFollowCounter->unread($id);

        $total += Comment
            ::where('to_user_slug', $slug)
            ->where('read', 0)
            ->count();

        return $total;
    }
}
