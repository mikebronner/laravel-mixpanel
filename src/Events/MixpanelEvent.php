<?php namespace GeneaLabs\LaravelMixpanel\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Collection;
use Carbon\Carbon;

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
        $this->profileData = $profileData;
        $this->trackingData = $this->addTimestamp($trackingData);
        $this->user = $user;
    }

    private function addTimestamp(array $trackingData) : array
    {
        return array_map(function ($data) {
            if (! array_key_exists('time', $data)) {
                $data['time'] = time();
            }

            return $data;
        }, $trackingData);
    }
}
