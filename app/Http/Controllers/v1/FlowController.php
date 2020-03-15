<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Repositories\FlowRepository;
use App\Http\Repositories\PinRepository;
use App\Http\Repositories\TagRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
}
