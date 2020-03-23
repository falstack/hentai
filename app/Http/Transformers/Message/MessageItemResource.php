<?php


namespace App\Http\Transformers\Message;


use App\Http\Modules\RichContentService;
use App\Http\Transformers\User\UserItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageItemResource extends JsonResource
{
    public function toArray($request)
    {
        $richContentService = new RichContentService();

        return [
            'id' => $this->id,
            'user' => new UserItemResource($this->sender),
            'content' => $richContentService->parseRichContent($this->content->text),
            'channel' => $this->when(isset($this->channel), $this->channel),
            'created_at' => $this->created_at
        ];
    }
}
