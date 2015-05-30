<?php namespace GeneaLabs\MixPanel;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class MixPanelUserEventHandler
{
    protected $mixPanel;

    /**
     *
     */
    public function __construct()
    {
        $this->mixPanel = App::make(MixPanel::class);
    }

    /**
     * @param array $event
     */
    public function onUserLoginAttempt(array $event)
    {
        $email = (array_key_exists('email', $event) ? $event['email'] : '');
        $password = (array_key_exists('password', $event) ? $event['password'] : '');

        $guard = App::make(Guard::class);
        $user = App::make(config('auth.model'))->where('email', $email)->first();

        if ($user
            && ! $guard->getProvider()->validateCredentials($user, ['email' => $email, 'password' => $password])
        ) {
            $this->mixPanel->identify($user->id);
            $this->mixPanel->track('Login Failed');
        }
    }

    /**
     * @param Model $user
     */
    public function onUserLogin(Model $user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->people->set($user->id, [
            '$first_name' => $user->first_name,
            '$last_name' => $user->last_name,
            '$email' => $user->email,
            '$created' => $user->created_at->format('Y-m-d\Th:i:s'),
        ]);
        $this->mixPanel->track('Login Succeeded');
    }

    /**
     * @param Model $user
     */
    public function onUserLogout(Model $user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->track('Logout Succeeded');
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen('auth.attempt', 'GeneaLabs\MixPanel\MixPanelUserEventHandler@onUserLoginAttempt');
        $events->listen('auth.login', 'GeneaLabs\MixPanel\MixPanelUserEventHandler@onUserLogin');
        $events->listen('auth.logout', 'GeneaLabs\MixPanel\MixPanelUserEventHandler@onUserLogout');
    }
}
