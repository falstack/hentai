<?php

namespace App\Console\Jobs;

use App\Http\Modules\Counter\BangumiLikeCounter;
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
        $this->migrationCommentAgree();
        return true;
    }

    protected function migrationCommentAgree()
    {
        $data = DB
            ::table('followables')
            ->where('migration_state', '<>', 3)
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
            $authorSlug = Comment
                ::where('id', $row->followable_id)
                ->pluck('from_user_slug')
                ->first();

            $author_id = slug2id($authorSlug);
            if (!$author_id)
            {
                continue;
            }

            $pinCommentLikeCounter->set(
                $row->user_id,
                $row->followable_id,
                $author_id,
                1,
                $row->created_at
            );

            DB::table('followables')
                ->where('followable_type', 'App\Models\Comment')
                ->where('relation', 'upvote')
                ->where('user_id', $row->user_id)
                ->where('followable_id', $row->followable_id)
                ->update([
                    'migration_state' => 3
                ]);
        }

        return true;
    }
}
