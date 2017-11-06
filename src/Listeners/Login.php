<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;

class Login
{
    public function handle($login)
    {
        event(new MixpanelEvent($login->user, 'User Logged In'));
    }
}
