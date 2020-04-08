<?php

namespace App\Console\Jobs;

use App\Http\Modules\Counter\PinLikeCounter;
use App\Http\Repositories\PinRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Content;
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
        $this->migrationPinLike();
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
}
