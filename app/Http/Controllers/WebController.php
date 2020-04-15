<?php
/**
 * Created by PhpStorm.
 * User: yuistack
 * Date: 2019-05-10
 * Time: 16:08
 */

namespace App\Http\Controllers;


use App\Console\Jobs\Test;
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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WebController extends Controller
{
    public function index(Request $request)
    {
//        $test = new Test();
//        $test->handle();
        return $this->resOK('hope happy end');
    }
}
