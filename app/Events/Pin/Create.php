<?php

namespace App\Events\Pin;

use App\Models\Pin;
use App\User;
use Illuminate\Queue\SerializesModels;

class Create
{
    use SerializesModels;

    public $pin;
    public $user;
    public $bangumiSlug;
    public $doPublish;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Pin $pin, User $user, string $bangumiSlug, bool $publish)
    {
        $this->pin = $pin;
        $this->user = $user;
        $this->bangumiSlug = $bangumiSlug;
        $this->doPublish = $publish;
    }
}
