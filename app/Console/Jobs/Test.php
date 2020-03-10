<?php

namespace App\Console\Jobs;

use App\Models\Content;
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

    protected $ids;
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ids = $this->getDeleteIds();

        if (empty($ids))
        {
            return true;
        }

        $this->ids = $ids;

        while (!empty($this->ids))
        {
            Content::whereIn('id', $this->ids)->delete();

            $this->ids = $this->getDeleteIds();
        }

        return true;
    }

    protected function getDeleteIds()
    {
        // select MIN(id) AS id from `contents` group by `contentable_type`, `contentable_id` having COUNT(id) > 1
        $ids = DB
            ::table('contents')
            ->select(DB::raw('MIN(id) AS id'))
            ->groupBy(['contentable_type', 'contentable_id'])
            ->whereNull('deleted_at')
            ->havingRaw('COUNT(id) > 1')
            ->pluck('id')
            ->toArray();

        return $ids;
    }
}
