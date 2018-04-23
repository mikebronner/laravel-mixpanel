<?php namespace GeneaLabs\LaravelMixpanel\Events;

use Illuminate\Queue\SerializesModels;

class MixpanelEvent
{
    use SerializesModels;

    public $charge;
    public $profileData;
    public $trackingData;
    public $user;

    public function __construct($user, array $trackingData, int $charge = 0, array $profileData = [])
    {
        $this->charge = $charge;
        $this->trackingData = $trackingData;
        $this->profileData = $profileData;
        $this->user = $user;
    }
}
