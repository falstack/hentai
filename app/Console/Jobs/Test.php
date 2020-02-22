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
        $list = Tag
            ::where('migration_state', '<>', 8)
            ->whereNotNull('migration_slug')
            ->pluck('migration_slug', 'id')
            ->toArray();

        foreach ($list as $tagId => $bangumiSlug)
        {
            $bangumiId = Bangumi
                ::where('slug', $bangumiSlug)
                ->pluck('id')
                ->first();

            DB
                ::table('followables')
                ->where('followable_id', $tagId)
                ->where('followable_type', 'App\Models\Tag')
                ->where('relation', 'bookmark')
                ->update([
                    'relation' => 'like',
                    'followable_type' => 'App\Models\Bangumi',
                    'followable_id' => $bangumiId
                ]);

            Tag
                ::where('id', $tagId)
                ->update([
                    'migration_state' => 8
                ]);
        }

        return true;
    }
}
