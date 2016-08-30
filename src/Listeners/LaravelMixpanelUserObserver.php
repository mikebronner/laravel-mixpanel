<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;

class LaravelMixpanelUserObserver
{
    public function created($user)
    {
        $trackingData = [
            ['User', ['Status' => 'Registered']],
        ];
        event(new MixpanelEvent($user, $trackingData));
    }

    public function saving($user)
    {
        $trackingData = [
            ['User', ['Status' => 'Updated']],
        ];
        event(new MixpanelEvent($user, $trackingData));
    }

    public function deleting($user)
    {
        $trackingData = [
            ['User', ['Status' => 'Deactivated']],
        ];
        event(new MixpanelEvent($user, $trackingData));
    }

    public function restored($user)
    {
        $trackingData = [
            ['User', ['Status' => 'Reactivated']],
        ];
        event(new MixpanelEvent($user, $trackingData));
    }
}
