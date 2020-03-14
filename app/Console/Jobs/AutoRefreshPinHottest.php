<?php

/**
 * Created by PhpStorm.
 * User: yuistack
 * Date: 2018/1/2
 * Time: 下午8:49
 */

namespace App\Console\Jobs;

use App\Http\Repositories\FlowRepository;
use Illuminate\Console\Command;

class AutoRefreshPinHottest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AutoRefreshPinHottest';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto refresh pin hottest';
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $flowRepository = new FlowRepository();
        $list = $flowRepository->RedisSort($flowRepository::$pinHottestVisitKey, function ()
        {
            return [];
        });

        foreach ($list as $key)
        {
            $params = explode('-', explode(':', $key)[1]);
            $flowRepository->pinHottestIds($params[0], $params[1], $params[2], true);
            $flowRepository->SortRemove($flowRepository::$pinHottestVisitKey, $key);
        }

        return true;
    }
}
