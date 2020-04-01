<?php


namespace App\Events\Message;

use App\Models\Message;
use App\User;
use Illuminate\Queue\SerializesModels;
use phpDocumentor\Reflection\Types\Integer;

class Create
{
    use SerializesModels;

    public $message;
    public $sender;
    public $roomId;
    public $type;

    public function __construct(Message $message, User $sender, string $roomId, Integer $messageType)
    {
        $this->message = $message;
        $this->sender = $sender;
        $this->roomId = $roomId;
        $this->type = $messageType;
    }
}
