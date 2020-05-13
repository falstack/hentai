<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Modules\Spider\Auth\UserAuthLink;
use App\Http\Modules\Spider\Auth\UserIsBilibili;
use App\Http\Modules\Spider\Base\GetResourceService;
use App\Http\Modules\Spider\BilBiliResourceSpider;
use App\Http\Repositories\UserRepository;
use Illuminate\Http\Request;

class SpiderController extends Controller
{
    public function getUsers(Request $request)
    {
        $user = $request->user();
        if (!$user->is_admin)
        {
            return $this->resErrRole();
        }

        $getResourceService = new GetResourceService();
        $users = $getResourceService->getAllUser();
        $userRepository = new UserRepository();
        foreach ($users as $i => $user)
        {
            $users[$i]->self = $userRepository->item($user->self_id);
        }

        return $this->resOK([
            'user' => $users,
            'site' => [
                [
                    'id' => 1,
                    'name' => 'bilibili',
                    'path' => 'https://space.bilibili.com/{id}'
                ]
            ]
        ]);
    }

    public function getUserRule(Request $request)
    {
        $user = $request->user();
        $channel = $request->get('channel');
        if (!in_array($channel, ['bilibili']))
        {
            return $this->resErrBad();
        }

        $channelId = $user["{$channel}_id"];
        if (!$channelId)
        {
            return $this->resOK(null);
        }

        $getResourceService = new GetResourceService();
        $spider = $getResourceService->getUser($channelId, $channel);

        return $this->resOK($spider);
    }

    public function saveUserRule(Request $request)
    {
        $user = $request->user();
        $channel = $request->get('channel');
        $rule = $request->get('rule');

        if (!in_array($channel, ['bilibili']))
        {
            return $this->resErrBad();
        }

        $channelId = $user["{$channel}_id"];
        if (!$channelId)
        {
            return $this->resErrBad();
        }

        $service = null;
        if ($channel === 'bilibili')
        {
            $service = new BilBiliResourceSpider();
        }

        if (!$service)
        {
            return $this->resErrBad();
        }

        $service->setUser($channelId, $rule);

        return $this->resNoContent();
    }

    public function setUser(Request $request)
    {
        $user = $request->user();
        if ($user->cant('change_spider_user'))
        {
            return $this->resErrRole();
        }

        $userId = $request->get('user_id');
        $site = $request->get('site');
        $rule = $request->get('rule');

        $getResourceService = new GetResourceService($site);
        $user = $getResourceService->setUser($userId, $rule);

        return $this->resOK($user);
    }

    public function delUser(Request $request)
    {
        $user = $request->user();
        if ($user->cant('change_spider_user'))
        {
            return $this->resErrRole();
        }

        $userId = $request->get('user_id');
        $withData = $request->get('with_data');
        $site = $request->get('site');

        $getResourceService = new GetResourceService($site);
        $result = $getResourceService->delUser($userId, $withData);

        return $this->resOK($result);
    }

    public function refreshUserData(Request $request)
    {
        $user = $request->user();
        if ($user->cant('change_spider_user'))
        {
            return $this->resErrRole();
        }

        $userId = $request->get('user_id');
        $site = $request->get('site');

        $resourceSpider = null;
        if ($site === 1)
        {
            $resourceSpider = new BilBiliResourceSpider();
        }

        if ($resourceSpider === null)
        {
            return $this->resErrBad();
        }

        $resourceSpider->updateOldResources(true, $userId);

        return $this->resNoContent();
    }

    public function setChannelCookie(Request $request)
    {
        $user = $request->user();
        if ($user->cant('set_oauth_channel'))
        {
            return $this->resErrRole();
        }

        $channel = $request->get('channel');
        $data = $request->get('data');

        $userAuthLink = new UserAuthLink($channel);
        $result = $userAuthLink->setCookie($data);

        if (!$result)
        {
            return $this->resErrServiceUnavailable();
        }

        return $this->resNoContent();
    }

    public function getChannelList(Request $request)
    {
        $userIsBilibili = new UserIsBilibili();

        return $this->resOK([
            [
                'name' => 'bilibili',
                'status' => $userIsBilibili->status()
            ]
        ]);
    }
}
