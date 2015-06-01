<?php namespace GeneaLabs\MixPanel;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\HTTP\Request;

class MixPanelUserEventHandler
{
    protected $guard;
    protected $mixPanel;
    protected $request;

    /**
     *
     */
    public function __construct(Guard $guard, MixPanel $mixPanel, Request $request)
    {
        $this->guard = $guard;
        $this->mixPanel = $mixPanel;
        $this->request = $request;
    }

    /**
     * @param array $event
     */
    public function onUserLoginAttempt(array $event)
    {
        $email = (array_key_exists('email', $event) ? $event['email'] : '');
        $password = (array_key_exists('password', $event) ? $event['password'] : '');

        $user = App::make(config('auth.model'))->where('email', $email)->first();

        if ($user
            && ! $this->guard->getProvider()->validateCredentials($user, ['email' => $email, 'password' => $password])
        ) {
            $this->mixPanel->identify($user->id);
            $this->mixPanel->track('Session', ['Status' => 'Login Failed']);
        }
    }

    /**
     * @param Model $user
     */
    public function onUserLogin(Model $user)
    {
        if ($user->name) {
            $nameParts = explode(' ', $user->name);
            array_filter($nameParts);
            $lastName = array_pop($nameParts);
            $firstName = implode(' ', $nameParts);
            $user->first_name = $firstName;
            $user->last_name = $lastName;
        }

        $data = [
            '$first_name' => $user->first_name,
            '$last_name' => $user->last_name,
            '$email' => $user->email,
            '$created' => $user->created_at->format('Y-m-d\Th:i:s'),
        ];
        array_filter($data);

        $this->mixPanel->identify($user->id);
        $this->mixPanel->people->set($user->id, $data, $this->request->ip);
        $this->mixPanel->track('Session', ['Status' => 'Logged In']);
    }

    /**
     * @param Model $user
     */
    public function onUserLogout(Model $user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->track('Session', ['Status' => 'Logged Out']);
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
