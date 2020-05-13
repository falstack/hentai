<?php


namespace App\Events\Spider;

use Illuminate\Queue\SerializesModels;

class AddUser
{
    use SerializesModels;

    public $siteType;
    public $userId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($siteType, $userId)
    {
        $this->siteType = $siteType;
        $this->userId = $userId;
    }
}
