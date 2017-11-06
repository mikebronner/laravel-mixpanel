<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Mixpanel;
use Illuminate\Auth\Events\Attempting;

class LoginAttempt
{
    public function handle(Attempting $event)
    {
        $email = $event->credentials['email'] ?? $event['email'] ?? '';

        $authModel = config('auth.providers.users.model', config('auth.model'));
        $user = app($authModel)
            ->where('email', $email)
            ->first();
        $eventName = 'Login Attempted';

        event(new Mixpanel($user, $eventName));
    }
}
