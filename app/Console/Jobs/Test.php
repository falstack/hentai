<?php

namespace App\Console\Jobs;

use App\Http\Modules\Counter\PinCommentLikeCounter;
use App\Http\Modules\Counter\PinLikeCounter;
use App\Http\Modules\Counter\PinMarkCounter;
use App\Http\Modules\Counter\PinRewardCounter;
use App\Http\Modules\Counter\UserFollowCounter;
use App\Models\Comment;
use App\Models\Pin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Test';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test job';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $result = $this->migrationPinLike();
        if ($result)
        {
            return true;
        }

        $result = $this->migrationPinReward();
        if ($result)
        {
            return true;
        }

        $result = $this->migrationPinBookmark();
        if ($result)
        {
            return true;
        }

        $result = $this->migrationCommentAgree();
        if ($result)
        {
            return true;
        }

        $result = $this->migrationUserFollow();
        if ($result)
        {
            return true;
        }

        $result = $this->migrationBangumiLike();
        if ($result)
        {
            return true;
        }

        return true;
    }

    protected function migrationPinLike()
    {
        $data = DB
            ::table('followables')
            ->where('migration_state', 0)
            ->where('followable_type', 'App\Models\Pin')
            ->where('relation', 'upvote')
            ->get()
            ->toArray();

        if (empty($data))
        {
            return false;
        }

        $pinLikeCounter = new PinLikeCounter();
        foreach ($data as $row)
        {
            $authorSlug = Pin::where('id', $row->followable_id)->pluck('user_slug')->first();

            $pinLikeCounter->set(
                $row->user_id,
                $row->followable_id,
                slug2id($authorSlug),
                1,
                $row->created_at
            );

            DB::table('followables')
                ->where('followable_type', 'App\Models\Pin')
                ->where('relation', 'upvote')
                ->where('user_id', $row->user_id)
                ->where('followable_id', $row->followable_id)
                ->update([
                    'migration_state' => 1
                ]);
        }

        return true;
    }

    protected function migrationPinReward()
    {
        $data = DB
            ::table('followables')
            ->where('migration_state', 0)
            ->where('followable_type', 'App\Models\Pin')
            ->where('relation', 'favorite')
            ->get()
            ->toArray();

        if (empty($data))
        {
            return false;
        }

        $pinRewardCounter = new PinRewardCounter();
        foreach ($data as $row)
        {
            $authorSlug = Pin::where('id', $row->followable_id)->pluck('user_slug')->first();

            $pinRewardCounter->set(
                $row->user_id,
                $row->followable_id,
                slug2id($authorSlug),
                1,
                $row->created_at
            );

            DB::table('followables')
                ->where('followable_type', 'App\Models\Pin')
                ->where('relation', 'favorite')
                ->where('user_id', $row->user_id)
                ->where('followable_id', $row->followable_id)
                ->update([
                    'migration_state' => 1
                ]);
        }

        return true;
    }

    protected function migrationPinBookmark()
    {
        $data = DB
            ::table('followables')
            ->where('migration_state', 0)
            ->where('followable_type', 'App\Models\Pin')
            ->where('relation', 'bookmark')
            ->get()
            ->toArray();

        if (empty($data))
        {
            return false;
        }

        $pinMarkCounter = new PinMarkCounter();
        foreach ($data as $row)
        {
            $authorSlug = Pin::where('id', $row->followable_id)->pluck('user_slug')->first();

            $pinMarkCounter->set(
                $row->user_id,
                $row->followable_id,
                slug2id($authorSlug),
                1,
                $row->created_at
            );

            DB::table('followables')
                ->where('followable_type', 'App\Models\Pin')
                ->where('relation', 'bookmark')
                ->where('user_id', $row->user_id)
                ->where('followable_id', $row->followable_id)
                ->update([
                    'migration_state' => 1
                ]);
        }

        return true;
    }

    protected function migrationCommentAgree()
    {
        $data = DB
            ::table('followables')
            ->where('migration_state', 0)
            ->where('followable_type', 'App\Models\Comment')
            ->where('relation', 'upvote')
            ->get()
            ->toArray();

        if (empty($data))
        {
            return false;
        }

        $pinCommentLikeCounter = new PinCommentLikeCounter();
        foreach ($data as $row)
        {
            $pinSlug = Comment::where('id', $row->followable_id)->pluck('pin_slug')->first();
            $authorSlug = Pin::where('slug', $pinSlug)->pluck('user_slug')->first();

            $pinCommentLikeCounter->set(
                $row->user_id,
                $row->followable_id,
                slug2id($authorSlug),
                1,
                $row->created_at
            );

            DB::table('followables')
                ->where('followable_type', 'App\Models\Comment')
                ->where('relation', 'upvote')
                ->where('user_id', $row->user_id)
                ->where('followable_id', $row->followable_id)
                ->update([
                    'migration_state' => 1
                ]);
        }

        return true;
    }

    protected function migrationUserFollow()
    {
        $data = DB
            ::table('followables')
            ->where('migration_state', 0)
            ->where('followable_type', 'App\User')
            ->where('relation', 'follow')
            ->get()
            ->toArray();

        if (empty($data))
        {
            return false;
        }

        $pinCommentLikeCounter = new UserFollowCounter();
        foreach ($data as $row)
        {
            $pinCommentLikeCounter->set(
                $row->user_id,
                0,
                $row->followable_id,
                1,
                $row->created_at
            );

            DB::table('followables')
                ->where('followable_type', 'App\User')
                ->where('relation', 'follow')
                ->where('user_id', $row->user_id)
                ->where('followable_id', $row->followable_id)
                ->update([
                    'migration_state' => 1
                ]);
        }

        return true;
    }

    protected function migrationBangumiLike()
    {
        $data = DB
            ::table('followables')
            ->where('migration_state', 0)
            ->where('followable_type', 'App\Models\Bangumin')
            ->where('relation', 'like')
            ->get()
            ->toArray();

        if (empty($data))
        {
            return false;
        }

        $pinLikeCounter = new PinLikeCounter();
        foreach ($data as $row)
        {
            $pinLikeCounter->set(
                $row->user_id,
                $row->followable_id,
                0,
                1,
                $row->created_at
            );

            DB::table('followables')
                ->where('followable_type', 'App\Models\Bangumin')
                ->where('relation', 'like')
                ->where('user_id', $row->user_id)
                ->where('followable_id', $row->followable_id)
                ->update([
                    'migration_state' => 1
                ]);
        }

        return true;
    }
}
