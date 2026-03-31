<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Mixpanel;

class LaravelMixpanelUserObserver
{
    public function created($user)
    {
        if (config("mixpanel.enable-default-tracking")) {
            event(new Mixpanel($user, ['User: Registered' => []]));
        }
    }

    public function saving($user)
    {
        if (config("mixpanel.enable-default-tracking")) {
            event(new Mixpanel($user, ['User: Updated' => []]));
        }
    }

    public function deleting($user)
    {
        if (config("mixpanel.enable-default-tracking")) {
            event(new Mixpanel($user, ['User: Deactivated' => []]));
        }
    }

    public function restored($user)
    {
        if (config("mixpanel.enable-default-tracking")) {
            event(new Mixpanel($user, ['User: Reactivated' => []]));
        }
    }
}
