<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;

class Logout
{
    public function handle($logout)
    {
        event(new MixpanelEvent($logout->user, 'User Logged Out'));
    }
}
