<?php


namespace App\Events\Pin;


use App\Models\Pin;
use App\User;
use Illuminate\Queue\SerializesModels;

class Move
{
    use SerializesModels;

    public $pin;
    public $user;
    public $oldBangumiSlug;
    public $newBangumiSlug;

    public function __construct(Pin $pin, User $user, string $oldBangumiSlug, string $newBangumiSlug)
    {
        $this->pin = $pin;
        $this->user = $user;
        $this->oldBangumiSlug = $oldBangumiSlug;
        $this->newBangumiSlug = $newBangumiSlug;
    }
}
