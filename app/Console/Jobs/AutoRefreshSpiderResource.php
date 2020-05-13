<?php

namespace App\Console\Jobs;

use App\Http\Modules\Counter\BangumiLikeCounter;
use App\Http\Modules\Counter\PinCommentLikeCounter;
use App\Http\Modules\Counter\PinLikeCounter;
use App\Http\Modules\Counter\PinMarkCounter;
use App\Http\Modules\Counter\PinRewardCounter;
use App\Http\Modules\Counter\UserFollowCounter;
use App\Http\Modules\Spider\Base\GetResourceService;
use App\Models\Comment;
use App\Models\Pin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoRefreshSpiderResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AutoRefreshSpiderResource';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto refresh spider resource';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $service = new GetResourceService();
        $service->autoload();
        return true;
    }
}
