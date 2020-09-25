<?php
/**
 * Created by PhpStorm.
 * User: yuistack
 * Date: 2019-05-10
 * Time: 16:08
 */

namespace App\Http\Controllers;


use App\Console\Jobs\Test;
use App\Http\Modules\Spider\Auth\UserIsBilibili;
use App\Http\Modules\Spider\BilBiliResourceSpider;
use App\Http\Repositories\BangumiRepository;
use App\Http\Repositories\MessageRepository;
use App\Http\Repositories\PinRepository;
use App\Http\Repositories\TagRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Bangumi;
use App\Models\Pin;
use App\Models\Tag;
use App\Services\Spider\BangumiSource;
use App\Services\Spider\Query;
use App\User;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function index()
    {
        return $this->resOK(sprintf('%.1f', '1.123') * 1000);
    }
}
