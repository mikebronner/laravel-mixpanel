<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;

class LaravelMixpanelUserObserver
{
    public function created($user)
    {
        event(new MixpanelEvent($user, 'User: Registered'));
    }

    public function saving($user)
    {
        event(new MixpanelEvent($user, 'User: Updated'));
    }

    public function deleting($user)
    {
        event(new MixpanelEvent($user, 'User: Deactivated'));
    }

    public function restored($user)
    {
        event(new MixpanelEvent($user, 'User: Reactivated'));
    }
}
