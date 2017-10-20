<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;

class LoginAttempt
{
    public function handle($event)
    {
        $email = $event->credentials['email'] ?? $event['email'] ?? '';

        $authModel = config('auth.providers.users.model', config('auth.model'));
        $user = app($authModel)
            ->where('email', $email)
            ->first();
        $eventName = 'Login Attempted';

        event(new MixpanelEvent($user, $eventName));
    }
}
