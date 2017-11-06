<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Mixpanel;
use Illuminate\Auth\Events\Login as LoginEvent;

class Login
{
    public function handle(LoginEvent $login)
    {
        event(new Mixpanel($login->user, 'User Logged In'));
    }
}
