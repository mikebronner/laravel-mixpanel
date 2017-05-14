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
        $email = $event->credentials['email'] ?? '';
        $password = $event->credentials['password'] ?? '';

        if (starts_with(app()->version(), '5.1.')) {
            $email = $event['email'] ?? '';
            $password = $event['password'] ?? '';
        }

        $authModel = config('auth.providers.users.model') ?? config('auth.model');
        $user = app($authModel)
            ->where('email', $email)
            ->first();
        $trackingData = [
            ['Session', ['Status' => 'Login Attempt Succeeded']],
        ];

        if ($user && auth()->guest()) {
            $trackingData = [
                ['Session', ['Status' => 'Login Attempt Failed']],
            ];
        }

        event(new MixpanelEvent($user, $trackingData));
    }

    public function onUserLogin($login)
    {
        $user = $login->user ?? $login;
        $trackingData = [['Session', ['Status' => 'Logged In']]];
        event(new MixpanelEvent($user, $trackingData));
    }

    public function onUserLogout($logout)
    {
        $user = property_exists($logout, 'user') ? $logout->user : $logout;
        $trackingData = [['Session', ['Status' => 'Logged Out']]];
        event(new MixpanelEvent($user, $trackingData));
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen('auth.attempt', self::class . '@onUserLoginAttempt');
        $events->listen('auth.login', self::class . '@onUserLogin');
        $events->listen('auth.logout', self::class . '@onUserLogout');

        $events->listen(Attempting::class, self::class . '@onUserLoginAttempt');
        $events->listen(Login::class, self::class . '@onUserLogin');
        $events->listen(Logout::class, self::class . '@onUserLogout');
    }
}
