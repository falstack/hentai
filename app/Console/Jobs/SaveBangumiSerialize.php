<?php
/**
 * file description
 *
 * @version
 * @author daryl
 * @date 2020-05-21
 * @since 2020-05-21 description
 */

namespace App\Console\Jobs;


use App\Services\Spider\BangumiSerialization\Acfun;
use App\Services\Spider\BangumiSerialization\Bilibili;
use Illuminate\Console\Command;

class SaveBangumiSerialize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SaveBangumiSerialize';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'save bangumi score';
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        (new Bilibili())->handle();
        (new Acfun())->handle();
    }
}