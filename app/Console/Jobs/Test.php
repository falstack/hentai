<?php

namespace App\Console\Jobs;

use App\Http\Repositories\PinRepository;
use App\Models\Bangumi;
use App\Models\BangumiQuestion;
use App\Models\Pin;
use App\Models\Tag;
use App\Services\Spider\Query;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $list = Bangumi
            ::whereNull('published_at')
            ->whereNotNull('source_id')
            ->inRandomOrder()
            ->take(1000)
            ->get();

        $query = new Query();
        foreach ($list as $bangumi)
        {
            $info = $query->getBangumiDetail($bangumi->source_id);
            if ($info['published_at'])
            {
                $bangumi->update([
                    'published_at' => $info['published_at']
                ]);
            }
            else
            {
                Log::info('update publish error：' . $bangumi->source_id);
            }
        }

        return true;
    }
}
