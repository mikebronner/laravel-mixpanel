<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Mixpanel;
use Illuminate\Auth\Events\Login as LoginEvent;

class Login
{
    public function handle(LoginEvent $login)
    {
        if (config("services.mixpanel.enable-default-tracking")) {
            event(new Mixpanel($login->user, ['User Logged In' => []]));
        }
    }
}
