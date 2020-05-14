<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Repository;
use App\Models\CMBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CmController extends Controller
{
    protected $bannerCacheKey = 'homepage_banners';

    public function showBanners(Request $request)
    {
        $repository = new Repository();
        $result = $repository->RedisItem($this->bannerCacheKey, function ()
        {
            $list = CMBanner
                ::where('online', 1)
                ->orderBy('id', 'DESC')
                ->select('id', 'type', 'image', 'title', 'link')
                ->get()
                ->toArray();

            return json_encode($list);
        });

        return $this->resOK(json_decode($result));
    }

    public function reportStat(Request $request)
    {
        $id = $request->get('id');
        $type = $request->get('type');
        if (!in_array($type, ['visit', 'click']))
        {
            return $this->resNoContent();
        }

        $banner = CMBanner::where('id', $id)->first();
        if (!$banner)
        {
            return $this->resNoContent();
        }

        $banner->increment("{$type}_count");

        return $this->resNoContent();
    }

    public function toggleBanner(Request $request)
    {
        $user = $request->user();
        if ($user->cant('change_homepage_banner'))
        {
            return $this->resErrRole();
        }

        $id = $request->get('id');
        $status = $request->get('status');

        CMBanner
            ::where('id', $id)
            ->update([
                'online' => $status
            ]);

        Redis::DEL($this->bannerCacheKey);

        return $this->resNoContent();
    }

    public function createBanner(Request $request)
    {
        $user = $request->user();
        if ($user->cant('change_homepage_banner'))
        {
            return $this->resErrRole();
        }

        $type = $request->get('type');
        $image = $request->get('image');
        $title = $request->get('title');
        $link = $request->get('link');

        $data = CMBanner::create([
            'type' => $type,
            'image' => $image,
            'title' => trim($title),
            'link' => trim($link)
        ]);

        return $this->resCreated($data);
    }

    public function updateBanner(Request $request)
    {
        $user = $request->user();
        if ($user->cant('change_homepage_banner'))
        {
            return $this->resErrRole();
        }

        $id = $request->get('id');
        $type = $request->get('type');
        $image = $request->get('image');
        $title = $request->get('title');
        $link = $request->get('link');

        CMBanner
            ::where('id', $id)
            ->update([
                'type' => $type,
                'image' => $image,
                'title' => trim($title),
                'link' => trim($link)
            ]);

        Redis::DEL($this->bannerCacheKey);

        return $this->resNoContent();
    }

    public function allBanners(Request $request)
    {
        $list = CMBanner
            ::orderBy('id', 'DESC')
            ->get()
            ->toArray();

        return $this->resOK($list);
    }
}
