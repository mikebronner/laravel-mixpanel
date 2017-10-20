<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;

class Logout
{
    public function handle($logout)
    {
        $user = $logout->user ?? $logout;
        event(new MixpanelEvent($user, 'User Logged Out'));
    }
}
