<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Mixpanel;
use Illuminate\Auth\Events\Logout as LogoutEvent;

class Logout
{
    public function handle(LogoutEvent $logout)
    {
        event(new Mixpanel($logout->user, ['User Logged Out' => []]));
    }
}
