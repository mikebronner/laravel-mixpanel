<?php namespace GeneaLabs\MixPanel;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class MixPanelUserEventHandler
{
    protected $mixPanel;

    public function __construct()
    {
        $this->mixPanel = App::make(MixPanel::class);
    }

    public function onUserLoginAttempt($event)
    {
        $guard = App::make(Guard::class);
        $user = App::make(config('auth.model'))->where('email', $event['email'])->first();

        if ($user && ! $guard->getProvider()->validateCredentials($user, $event)) {
            $this->mixPanel->identify($user->id);
            $this->mixPanel->track('User Login Failed');
        }
    }

    public function onUserLogin($user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->track('User Loged In');
    }

    public function onUserLogout($user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->track('User Logged Out');
    }

    public function subscribe($events)
    {
        $events->listen('auth.attempt', 'GeneaLabs\MixPanel\MixPanelUserEventHandler@onUserLoginAttempt');
        $events->listen('auth.login', 'GeneaLabs\MixPanel\MixPanelUserEventHandler@onUserLogin');
        $events->listen('auth.logout', 'GeneaLabs\MixPanel\MixPanelUserEventHandler@onUserLogout');
    }
}
