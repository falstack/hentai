<?php

namespace App\Console\Jobs;

use App\Http\Repositories\PinRepository;
use App\Models\Bangumi;
use App\Models\BangumiQuestion;
use App\Models\Pin;
use App\Models\Tag;
use App\User;
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
        $list = User
            ::where('migration_state', '<>', 8)
            ->take(5000)
            ->pluck('id')
            ->toArray();

        foreach ($list as $userId)
        {
            $count = DB
                ::table('followables')
                ->where('user_id', $userId)
                ->where('followable_type', 'App\Models\Bangumi')
                ->where('relation', 'like')
                ->count();

            User
                ::where('id', $userId)
                ->update([
                    'level' => $count + 1,
                    'migration_state' => 8
                ]);
        }

        return true;
    }
}
