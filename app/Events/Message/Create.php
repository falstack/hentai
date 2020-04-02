<?php


namespace App\Events\Message;

use App\Models\Message;
use App\User;
use Illuminate\Queue\SerializesModels;

class Create
{
    use SerializesModels;

    public $message;
    public $sender;
    public $roomId;
    public $type;

    public function __construct(Message $message, User $sender, string $roomId, int $messageType)
    {
        $this->message = $message;
        $this->sender = $sender;
        $this->roomId = $roomId;
        $this->type = $messageType;
    }
}
