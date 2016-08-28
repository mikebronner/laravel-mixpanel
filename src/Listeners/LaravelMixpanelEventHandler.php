<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class LaravelMixpanelEventHandler
{
    protected $guard;
    protected $mixPanel;
    protected $request;

    public function __construct(Request $request, Guard $guard, LaravelMixpanel $mixPanel)
    {
        $this->guard = $guard;
        $this->mixPanel = $mixPanel;
        $this->request = $request;
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

        $user = app(config('auth.model'))
            ->where('email', $email)
            ->first();

        if ($user
            && ! $this->guard->getProvider()->validateCredentials($user, ['email' => $email, 'password' => $password])
        ) {
            $this->mixPanel->identify($user->getKey());
            $this->mixPanel->track('Session', ['Status' => 'Login Failed']);
        }
    }

    public function onUserLogin($login)
    {
        if (starts_with(app()->version(), '5.1.')) {
            $user = $login;
        }

        if (starts_with(app()->version(), '5.3.')) {
            $user = $login->user;
        }

        $firstName = $user->first_name;
        $lastName = $user->last_name;

        if ($user->name) {
            $nameParts = explode(' ', $user->name);
            array_filter($nameParts);
            $lastName = array_pop($nameParts);
            $firstName = implode(' ', $nameParts);
        }

        $data = [
            '$first_name' => $firstName,
            '$last_name' => $lastName,
            '$name' => $user->name,
            '$email' => $user->email,
            '$created' => ($user->created_at
                ? $user->created_at->format('Y-m-d\Th:i:s')
                : null),
        ];
        array_filter($data);
        $this->mixPanel->identify($user->getKey());
        $this->mixPanel->people->set($user->getKey(), $data, $this->request->ip());
        $this->mixPanel->track('Session', ['Status' => 'Logged In']);
    }

    public function onUserLogout($logout)
    {
        if (starts_with(app()->version(), '5.1.')) {
            $user = $logout;
        }

        if (starts_with(app()->version(), '5.3.')) {
            $user = $logout->user;
        }

        if ($user) {
            $this->mixPanel->identify($user->getKey());
        }

        $this->mixPanel->track('Session', ['Status' => 'Logged Out']);
    }

    public function onViewLoad($routeMatched)
    {
        $route = '';

        if (auth()->check()) {
            $this->mixPanel->identify(auth()->user()->getKey());
            $this->mixPanel->people->set(auth()->user()->getKey(), [], $this->request->ip());
        }

        if (starts_with(app()->version(), '5.1.')) {
            $route = $routeMatched->uri;
        }

        if (starts_with(app()->version(), '5.3.')) {
            $route = $routeMatched->route;
            $routeAction = $route->getAction();
            $route = (is_array($routeAction) && array_key_exists('as', $routeAction) ? $routeAction['as'] : null);
        }

        $this->mixPanel->track('Page View', ['Route' => $route]);
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen('auth.attempt', self::class . '@onUserLoginAttempt');
        $events->listen('auth.login', self::class . '@onUserLogin');
        $events->listen('auth.logout', self::class . '@onUserLogout');
        $events->listen('router.matched', self::class . '@onViewLoad');

        $events->listen(Attempting::class, self::class . '@onUserLoginAttempt');
        $events->listen(Login::class, self::class . '@onUserLogin');
        $events->listen(Logout::class, self::class . '@onUserLogout');
        $events->listen(RouteMatched::class, self::class . '@onViewLoad');
    }
}
