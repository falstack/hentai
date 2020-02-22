<?php

namespace App\Console\Jobs;

use App\Http\Repositories\PinRepository;
use App\Models\Bangumi;
use App\Models\BangumiQuestion;
use App\Models\Pin;
use App\Models\Tag;
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
        $list = DB
            ::table('followables')
            ->where('followable_type', 'App\Models\Tag')
            ->where('relation', 'bookmark')
            ->get()
            ->toArray();

        foreach ($list as $relation)
        {
            $bangumiSlug = Tag
                ::where('id', $relation->followable_id)
                ->pluck('migration_slug')
                ->first();

            if (!$bangumiSlug)
            {
                continue;
            }

            $bangumiId = Bangumi
                ::where('slug', $bangumiSlug)
                ->pluck('id')
                ->first();

            DB
                ::table('followables')
                ->where('id', $relation->id)
                ->update([
                    'relation' => 'like',
                    'followable_type' => 'App\Models\Bangumi',
                    'followable_id' => $bangumiId
                ]);
        }

        return true;
    }
}
