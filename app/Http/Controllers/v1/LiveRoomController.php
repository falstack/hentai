<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Repositories\LiveRoomRepository;
use App\Models\IdolVoice;
use App\Models\LiveRoom;
use App\Services\Qiniu\Qshell;
use Illuminate\Http\Request;

class LiveRoomController extends Controller
{
    public function show(Request $request)
    {
        $id = $request->get('id');

        $liveRoomRepository = new LiveRoomRepository();

        $live = $liveRoomRepository->item($id);

        if (is_null($live))
        {
            return $this->resErrNotFound();
        }

        return $this->resOK($live);
    }

    /**
     * 拿角色的声源列表
     */
    public function idolVoiceList(Request $request)
    {
        $slug = $request->get('slug');

        $list = IdolVoice
            ::withTrashed()
            ->where('from_slug', $slug)
            ->where('from_type', 0)
            ->get();

        return $this->resOK($list);
    }

    /**
     * 通过后台给角色上传声源
     */
    public function createIdolVoice(Request $request)
    {
        $user = $request->user();
        if ($user->cant('edit_voice'))
        {
            return $this->resErrRole();
        }

        $slug = $request->get('slug');
        $src = $request->get('src');
        $meta = $request->get('meta');
        $text = $request->get('text');

        $voice = IdolVoice::create([
            'from_slug' => $slug,
            'from_type' => 0,
            'src' => $src,
            'meta' => json_encode($meta),
            'text' => $text
        ]);

        $liveRoomRepository = new LiveRoomRepository();
        $liveRoomRepository->allVoice(0, '', true);

        return $this->resCreated($voice);
    }

    /**
     * 通过后台给角色更新声源
     */
    public function updateIdolVoice(Request $request)
    {
        $user = $request->user();
        if ($user->cant('edit_voice'))
        {
            return $this->resErrRole();
        }

        $id = $request->get('id');
        $src = $request->get('src');
        $meta = $request->get('meta');
        $text = $request->get('text');

        IdolVoice
            ::where('id', $id)
            ->update([
                'src' => $src,
                'meta' => $meta,
                'text' => $text
            ]);

        $voice = IdolVoice::where('id', $id)->first();

        return $this->resOK($voice);
    }

    /**
     * 通过后台删除角色的声源
     */
    public function deleteIdolVoice(Request $request)
    {
        $user = $request->user();
        if (!$user->is_admin)
        {
            return $this->resErrRole();
        }

        $id = $request->get('id');

        IdolVoice
            ::where('id', $id)
            ->delete();

        return $this->resOK();
    }

    public function createUserVoice(Request $request)
    {
        $file = $request->file('file');
        $user = $request->user();

        $qshell = new Qshell();
        $audio = $qshell->audio($file->path(), $user->id);

        $res = IdolVoice::create([
            'from_slug' => $user->slug,
            'from_type' => 1,
            'src' => $audio['url'],
            'meta' => json_encode([
                'size' => intval($audio['meta']['format']['size']),
                'duration' => intval($request->get('duration'))
            ]),
            'text' => ''
        ]);

        $meta = json_decode($res->meta);
        $res->duration = $meta->duration;
        $res->meta = $meta;
        $res->alias = '';
        $res->reader = [
            'id' => $user->id,
            'slug' => $user->slug,
            'name' => $user->nickname,
            'avatar' => $user->avatar
        ];

        $liveRoomRepository = new LiveRoomRepository();
        $liveRoomRepository->allVoice(1, $user->slug, true);

        return $this->resOK($res);
    }

    public function updateUserVoice(Request $request)
    {
        $user = $request->user();
        $id = $request->get('id');
        $text = $request->get('text');

        $audio = IdolVoice::where('id', $id)->first();
        if (is_null($audio))
        {
            return $this->resErrNotFound();
        }

        if ($audio->from_slug !== $user->slug)
        {
            return $this->resErrRole();
        }

        $audio->update([
            'text' => $text
        ]);

        $liveRoomRepository = new LiveRoomRepository();
        $liveRoomRepository->allVoice('1', $user->slug, true);

        return $this->resOK();
    }

    public function deleteUserVoice(Request $request)
    {
        $user = $request->user();
        $id = $request->get('id');

        $audio = IdolVoice::where('id', $id)->first();
        if (is_null($audio))
        {
            return $this->resErrNotFound();
        }

        if ($audio->from_slug !== $user->slug)
        {
            return $this->resErrRole();
        }

        $audio->delete();
        $liveRoomRepository = new LiveRoomRepository();
        $liveRoomRepository->allVoice('1', $user->slug, true);

        return $this->resOK();
    }

    public function publishLive(Request $request)
    {
        $user = $request->user();
        $id = $request->get('id');
        $readers = $request->get('readers');
        $content = $request->get('content');
        $title = $request->get('title');
        $desc = $request->get('desc');
        $isPublish = $request->get('is_publish');
        $liveRoomRepository = new LiveRoomRepository();

        if ($id)
        {
            $live = LiveRoom
                ::where('id', $id)
                ->first();

            if (is_null($live))
            {
                return $this->resErrNotFound();
            }

            if ($live->author_id != $user->id)
            {
                return $this->resErrRole();
            }

            $needPublish = $live->visit_state == 0 && $isPublish;
            $live->update([
                'content' => json_encode([
                    'content' => $content,
                    'readers' => $readers
                ]),
                'title' => $title,
                'desc' => $desc,
                'visit_state' => $isPublish ? 1 : 0
            ]);

            $liveRoomRepository->item($live->id, true);
            if ($needPublish)
            {
                // TODO：trial
            }

            return $this->resOK($live->id);
        }

        $live = LiveRoom::create([
            'content' => json_encode([
                'content' => $content,
                'readers' => $readers
            ]),
            'title' => $title,
            'desc' => $desc,
            'visit_state' => $isPublish ? 1 : 0,
            'author_id' => $user->id
        ]);

        if ($isPublish)
        {
            // TODO：refresh list cache
            // TODO：trial
        }

        return $this->resOK($live->id);
    }
    /**
     * 删除一个实时聊天
     */
    public function delete(Request $request)
    {
        $user = $request->user();
        $id = $request->get('id');

        $live = LiveRoom
            ::where('id', $id)
            ->first();

        if (is_null($live))
        {
            return $this->resErrNotFound();
        }

        if ($live->author_id !== $user->id)
        {
            return $this->resErrRole();
        }

        $live->delete();

        if ($live->visit_state != 0)
        {
            $liveRoomRepository = new LiveRoomRepository();
            $liveRoomRepository->item($live->id, true);
        }

        return $this->resNoContent();
    }

    /**
     * 用户的实时聊天列表
     */
    public function userLiveChat(Request $request)
    {

    }

    /**
     * 用户实时聊天草稿箱
     */
    public function drafts(Request $request)
    {
        $user = $request->user();
        $list = LiveRoom
            ::where('author_id', $user->id)
            ->where('visit_state', 0)
            ->select('id', 'title', 'desc', 'updated_at')
            ->orderBy('updated_at', 'DESC')
            ->get()
            ->toArray();

        return $this->resOK([
            'result' => $list
        ]);
    }

    /**
     * 热门的实时聊天列表
     */
    public function activeLiveChat(Request $request)
    {
        $seenIds = $request->get('seen_ids') ? explode(',', $request->get('seen_ids')) : [];
        $take = $request->get('take') ?: 10;
        $liveRoomRepository = new LiveRoomRepository();

        $idsObj = $liveRoomRepository->activityIds($take, $seenIds);

        if (empty($idsObj['result']))
        {
            return $this->resOK($idsObj);
        }

        $idsObj['result'] = $liveRoomRepository->list($idsObj['result']);

        return $this->resOK($idsObj);
    }

    public function allVoice(Request $request)
    {
        $type = $request->get('type');
        $slug = $request->get('slug');

        $liveRoomRepository = new LiveRoomRepository();
        $list = $liveRoomRepository->allVoice($type, $slug);

        return $this->resOK($list);
    }
}
