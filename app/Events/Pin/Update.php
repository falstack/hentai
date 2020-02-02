<?php

namespace App\Events\Pin;

use App\Models\Pin;
use App\User;
use Illuminate\Queue\SerializesModels;

class Update
{
    use SerializesModels;

    public $pin;
    public $user;
    public $doPublish;
    public $published;
    public $oldBangumiSlug;
    public $newBangumiSlug;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Pin $pin, User $user, bool $publish, string $oldBangumiSlug, string $newBangumiSlug)
    {
        $this->pin = $pin;
        $this->user = $user;
        $this->doPublish = $publish;
        $this->published = !!$pin->published_at;
        $this->oldBangumiSlug = $oldBangumiSlug;
        $this->newBangumiSlug = $newBangumiSlug;
    }
}
