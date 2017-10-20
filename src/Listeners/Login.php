<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;

class Login
{
    public function handle($login)
    {
        dd('test', $login);
        $user = $login->user ?? $login;
        event(new MixpanelEvent($user, 'User Logged In'));
    }
}
