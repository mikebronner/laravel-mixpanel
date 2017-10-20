<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class LaravelMixpanelEventHandler
{
    public function onUserLoginAttempt($event)
    {
        $email = $event->credentials['email'] ?? $event['email'] ?? '';

        $authModel = config('auth.providers.users.model', config('auth.model'));
        $user = app($authModel)
            ->where('email', $email)
            ->first();
        $eventName = 'Login Attempted';

        event(new MixpanelEvent($user, $eventName));
    }

    public function onUserLogin($login)
    {
        dd('test', $login);
        $user = $login->user ?? $login;
        event(new MixpanelEvent($user, 'User Logged In'));
    }

    public function onUserLogout($logout)
    {
        $user = $logout->user ?? $logout;
        event(new MixpanelEvent($user, 'User Logged Out'));
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Attempting::class, self::class . '@onUserLoginAttempt');
        $events->listen(Login::class, self::class . '@onUserLogin');
        $events->listen(Logout::class, self::class . '@onUserLogout');
    }
}
