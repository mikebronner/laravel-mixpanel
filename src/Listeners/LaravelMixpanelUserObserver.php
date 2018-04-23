<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Mixpanel;

class LaravelMixpanelUserObserver
{
    public function created($user)
    {
        event(new Mixpanel($user, ['User: Registered']));
    }

    public function saving($user)
    {
        event(new Mixpanel($user, ['User: Updated']));
    }

    public function deleting($user)
    {
        event(new Mixpanel($user, ['User: Deactivated']));
    }

    public function restored($user)
    {
        event(new Mixpanel($user, ['User: Reactivated']));
    }
}
