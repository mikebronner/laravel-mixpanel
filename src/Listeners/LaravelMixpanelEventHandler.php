<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Events\Dispatcher;
use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;

class LaravelMixpanelEventHandler
{
    protected $guard;

    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    public function onUserLoginAttempt($event)
    {
        if (starts_with(app()->version(), '5.1.')) {
            $email = $event['email'] ?? '';
            $password = $event['password'] ?? '';
        }

        if (starts_with(app()->version(), '5.3.')) {
            $email = $event->credentials['email'] ?? '';
            $password = $event->credentials['password'] ?? '';
        }

        $authModel = config('auth.providers.users.model') ?? config('auth.model');
        $user = app($authModel)
            ->where('email', $email)
            ->first();
        $trackingData = [
            ['Session', ['Status' => 'Login Attempt Succeeded']],
        ];

        if ($user
            && ! $this->guard->getProvider()->validateCredentials($user, ['email' => $email, 'password' => $password])
        ) {
            $trackingData = [
                ['Session', ['Status' => 'Login Attempt Failed']],
            ];
        }

        event(new MixpanelEvent($user, $trackingData));
    }

    public function onUserLogin($login)
    {
        if (starts_with(app()->version(), '5.1.')) {
            $user = $login;
        }

        if (starts_with(app()->version(), '5.3.')) {
            $user = $login->user;
        }

        $trackingData = [
            ['Session', ['Status' => 'Logged In']],
        ];
        event(new MixpanelEvent($user, $trackingData));
    }

    public function onUserLogout($logout)
    {
        if (starts_with(app()->version(), '5.1.')) {
            $user = $logout;
        }

        if (starts_with(app()->version(), '5.3.')) {
            $user = $logout->user;
        }

        $trackingData = [
            ['Session', ['Status' => 'Logged Out']],
        ];
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
