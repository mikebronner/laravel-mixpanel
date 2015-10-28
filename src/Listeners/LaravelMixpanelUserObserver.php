<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LaravelMixpanelUserObserver
{
    protected $mixPanel;
    protected $request;

    /**
     * @param Request         $request
     * @param LaravelMixpanel $mixPanel
     */
    public function __construct(Request $request, LaravelMixpanel $mixPanel)
    {
        $this->mixPanel = $mixPanel;
        $this->request = $request;
    }

    /**
     * @param Model $user
     */
    public function created(Model $user)
    {
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

        if (count($data)) {
            $this->mixPanel->people->set($user->getKey(), $data, $this->request->ip());
        }

        $this->mixPanel->track('User', ['Status' => 'Registered']);
    }

    /**
     * @param Model $user
     */
    public function saving(Model $user)
    {
        $this->mixPanel->identify($user->getKey());
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

        if (count($data)) {
            $this->mixPanel->people->set($user->getKey(), $data, $this->request->ip());
        }
    }

    /**
     * @param Model $user
     */
    public function deleting(Model $user)
    {
        $this->mixPanel->identify($user->getKey());
        $this->mixPanel->track('User', ['Status' => 'Deactivated']);
    }

    /**
     * @param Model $user
     */
    public function restored(Model $user)
    {
        $this->mixPanel->identify($user->getKey());
        $this->mixPanel->track('User', ['Status' => 'Reactivated']);
    }
}
