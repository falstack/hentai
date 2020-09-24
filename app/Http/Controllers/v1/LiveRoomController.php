<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Repositories\LiveRoomRepository;
use App\Models\IdolVoice;
use App\Services\Qiniu\Qshell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LiveRoomController extends Controller
{
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
        $userId = $request->get('user_id');

        $qshell = new Qshell();
        $res = $qshell->audio($file->path(), $userId);

        return $this->resOK([
            'all' => $request->all(),
            'res' => $res
        ]);
    }

    /**
     * 创建一个实时聊天
     */
    public function createLiveChat(Request $request)
    {

    }

    /**
     * 更新一个实时聊天
     */
    public function updateLiveChat(Request $request)
    {

    }

    /**
     * 发布一个实时聊天
     */
    public function publishLiveChat(Request $request)
    {

    }

    /**
     * 删除一个实时聊天
     */
    public function deleteLiveChat(Request $request)
    {

    }

    /**
     * 用户的实时聊天列表
     */
    public function userLiveChat(Request $request)
    {

    }

    /**
     * 用户的声源列表
     */
    public function userVoiceList(Request $request)
    {

    }

    /**
     * 用户实时聊天草稿箱
     */
    public function userLiveChatDraft(Request $request)
    {

    }

    /**
     * 热门的实时聊天列表
     */
    public function trendLiveChat(Request $request)
    {

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
