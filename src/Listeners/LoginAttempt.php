<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Mixpanel;
use Illuminate\Auth\Events\Attempting;

class LoginAttempt
{
    public function handle(Attempting $event)
    {
        if (config("services.mixpanel.enable-default-tracking")) {
            $email = data_get($event, "credentials.email")
                ?: data_get($event, "email")
                ?: "";
            $user = null;
            $authModel = config('auth.providers.users.model', config('auth.model'));

            if ($email) {
                $user = app($authModel)
                    ->where('email', $email)
                    ->first();
            }

            event(new Mixpanel($user, ['Login Attempted' => []]));
        }
    }
}
