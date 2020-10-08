<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Modules\RichContentService;
use App\Http\Modules\Spider\Base\GetResourceService;
use App\Http\Repositories\FlowRepository;
use App\Http\Repositories\IdolRepository;
use App\Http\Repositories\LiveRoomRepository;
use App\Http\Repositories\PinRepository;
use App\Models\Content;
use Illuminate\Http\Request;

class FlowController extends Controller
{
    public function pinNewest(Request $request)
    {
        $flowRepository = new FlowRepository();

        $take = $request->get('take') ?: 10;
        $slug = $request->get('slug') ?: $flowRepository::$indexSlug;
        $from = $request->get('from') ?: 'index';
        $isUp = $request->get('is_up') ?: 0;
        $lastId = $request->get('last_id') ?: '';

        if (!in_array($from, $flowRepository::$from))
        {
            return $this->resErrBad();
        }

        $idsObj = $flowRepository->pinNewest($from, $slug, $take, $lastId, (bool)$isUp);
        if (empty($idsObj['result']))
        {
            return $this->resOK($idsObj);
        }

        $pinRepository = new PinRepository();
        $idsObj['result'] = $pinRepository->list($idsObj['result']);

        return $this->resOK($idsObj);
    }

    public function pinActivity(Request $request)
    {
        $flowRepository = new FlowRepository();

        $take = $request->get('take') ?: 10;
        $slug = $request->get('slug') ?: $flowRepository::$indexSlug;
        $from = $request->get('from') ?: 'index';
        $randId = $request->get('rand_id') ?: 1;
        $seenIds = $request->get('seen_ids') ? explode(',', $request->get('seen_ids')) : [];

        if (!in_array($from, $flowRepository::$from))
        {
            return $this->resErrBad();
        }

        $idsObj = $flowRepository->pinActivity($from, $slug, $take, $seenIds, $randId);
        if (empty($idsObj['result']))
        {
            return $this->resOK($idsObj);
        }

        $pinRepository = new PinRepository();
        $idsObj['result'] = $pinRepository->list($idsObj['result']);

        return $this->resOK($idsObj);
    }

    public function pinHottest(Request $request)
    {
        $flowRepository = new FlowRepository();

        $take = $request->get('take') ?: 10;
        $slug = $request->get('slug') ?: $flowRepository::$indexSlug;
        $from = $request->get('from') ?: 'index';
        $randId = $request->get('rand_id') ?: 1;
        $seenIds = $request->get('seen_ids') ? explode(',', $request->get('seen_ids')) : [];

        if (!in_array($from, $flowRepository::$from))
        {
            return $this->resErrBad();
        }

        $idsObj = $flowRepository->pinHottest($from, $slug, $take, $seenIds, $randId);
        if (empty($idsObj['result']))
        {
            return $this->resOK($idsObj);
        }

        $pinRepository = new PinRepository();
        $idsObj['result'] = $pinRepository->list($idsObj['result']);

        return $this->resOK($idsObj);
    }

    public function pinTrial(Request $request)
    {
        $flowRepository = new FlowRepository();

        $take = $request->get('take') ?: 10;
        $slug = $request->get('slug') ?: $flowRepository::$indexSlug;
        $from = $request->get('from') ?: 'index';

        if (!in_array($from, $flowRepository::$from))
        {
            return $this->resErrBad();
        }

        $idsObj = $flowRepository->pinTrial($from, $slug, $take);
        if (empty($idsObj['result']))
        {
            return $this->resOK($idsObj);
        }

        $pinRepository = new PinRepository();
        $slugs = $idsObj['result'];
        $idsObj['result'] = $pinRepository->list($slugs);
        $ids = array_map(function ($slug)
        {
            return slug2id($slug);
        }, $slugs);

        $content = Content
            ::where('contentable_type', 'App\\Models\\Pin')
            ->whereIn('contentable_id', $ids)
            ->pluck('text', 'contentable_id')
            ->toArray();

        $richContentService = new RichContentService();
        $extra = [];
        foreach ($content as $id => $text)
        {
            $extra[$slugs[array_search($id, $ids)]] = $richContentService->detectContentRisk($text, false);
        }

        $idsObj['extra'] = $extra;

        return $this->resOK($idsObj);
    }

    public function idolNewest(Request $request)
    {
        $flowRepository = new FlowRepository();
        $take = $request->get('take') ?: 10;
        $lastId = $request->get('last_id') ?: '';

        $idsObj = $flowRepository->idolNewest($lastId, $take);
        if (empty($idsObj['result']))
        {
            return $this->resOK($idsObj);
        }

        $idolRepository = new IdolRepository();
        $idsObj['result'] = $idolRepository->list($idsObj['result']);

        return $this->resOK($idsObj);
    }

    public function idolActivity(Request $request)
    {
        $flowRepository = new FlowRepository();

        $take = $request->get('take') ?: 10;
        $slug = $request->get('slug') ?: $flowRepository::$indexSlug;
        $from = $request->get('from') ?: 'index';
        $seenIds = $request->get('seen_ids') ? explode(',', $request->get('seen_ids')) : [];

        if (!in_array($from, $flowRepository::$from))
        {
            return $this->resErrBad();
        }

        $idsObj = $flowRepository->idolActivity($from, $slug, $take, $seenIds);
        if (empty($idsObj['result']))
        {
            return $this->resOK($idsObj);
        }

        $idolRepository = new IdolRepository();
        $idsObj['result'] = $idolRepository->list($idsObj['result']);

        return $this->resOK($idsObj);
    }

    public function idolHottest(Request $request)
    {
        $flowRepository = new FlowRepository();

        $take = $request->get('take') ?: 10;
        $slug = $request->get('slug') ?: $flowRepository::$indexSlug;
        $from = $request->get('from') ?: 'index';
        $seenIds = $request->get('seen_ids') ? explode(',', $request->get('seen_ids')) : [];

        if (!in_array($from, $flowRepository::$from))
        {
            return $this->resErrBad();
        }

        $idsObj = $flowRepository->idolHottest($from, $slug, $take, $seenIds);
        if (empty($idsObj['result']))
        {
            return $this->resOK($idsObj);
        }

        $idolRepository = new IdolRepository();
        $idsObj['result'] = $idolRepository->list($idsObj['result']);

        return $this->resOK($idsObj);
    }

    public function spiderFlow(Request $request)
    {
        $sort = $request->get('sort') ?: 'newest';
        $slug = $request->get('slug') ?: 0;
        $page = $request->get('page') ?: 1;
        $take = $request->get('take') ?: 10;
        $randId = $request->get('rand_id') ?: 1;

        $getResourceService = new GetResourceService();
        $dataObj = $getResourceService->getFlowData($sort, $slug, $page - 1, $take, $randId);

        return $this->resOK($dataObj);
    }

    public function spiderReport(Request $request)
    {
        $ids = $request->get('id') ? explode(',', $request->get('id')) : [];
        $type = $request->get('type');

        $getResourceService = new GetResourceService();
        $getResourceService->reportResource($ids, $type);

        return $this->resNoContent();
    }

    public function spiderHots(Request $request)
    {
        $day = $request->get('day');
        if (!in_array(intval($day), [1, 3, 7]))
        {
            return $this->resErrBad();
        }

        $getResourceService = new GetResourceService();
        $result = $getResourceService->spiderHots($day);

        return $this->resOK([
            'result' => $result,
            'no_more' => true,
            'total' => 0
        ]);
    }

    public function liveNewest(Request $request)
    {
        $flowRepository = new FlowRepository();

        $take = $request->get('take') ?: 10;
        $id = $request->get('id') ?: $flowRepository::$indexSlug;
        $from = $request->get('from') ?: 'index';
        $lastId = $request->get('last_id') ?: '';

        if (!in_array($from, $flowRepository::$from))
        {
            return $this->resErrBad();
        }

        $idsObj = $flowRepository->liveNewest($from, $id, $take, $lastId);
        if (empty($idsObj['result']))
        {
            return $this->resOK($idsObj);
        }

        $liveRoomRepository = new LiveRoomRepository();
        $idsObj['result'] = $liveRoomRepository->list($idsObj['result']);

        return $this->resOK($idsObj);
    }

    public function liveActivity(Request $request)
    {
        $flowRepository = new FlowRepository();

        $take = $request->get('take') ?: 10;
        $slug = $request->get('slug') ?: $flowRepository::$indexSlug;
        $from = $request->get('from') ?: 'index';
        $seenIds = $request->get('seen_ids') ? explode(',', $request->get('seen_ids')) : [];

        if (!in_array($from, $flowRepository::$from))
        {
            return $this->resErrBad();
        }

        $idsObj = $flowRepository->liveActivity($from, $slug, $take, $seenIds);
        if (empty($idsObj['result']))
        {
            return $this->resOK($idsObj);
        }

        $liveRoomRepository = new LiveRoomRepository();
        $idsObj['result'] = $liveRoomRepository->list($idsObj['result']);

        return $this->resOK($idsObj);
    }
}
