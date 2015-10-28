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
        $firstName = $user->getAttribute('first_name');
        $lastName = $user->getAttribute('last_name');

        if ($user->getAttribute('name')) {
            $nameParts = explode(' ', $user->getAttribute('name'));
            array_filter($nameParts);
            $lastName = array_pop($nameParts);
            $firstName = implode(' ', $nameParts);
        }

        $data = [
            '$first_name' => $firstName,
            '$last_name' => $lastName,
            '$name' => $user->getAttribute('name'),
            '$email' => $user->getAttribute('email'),
            '$created' => ($user->getAttribute('created_at')
                ? $user->getAttribute('created_at')->format('Y-m-d\Th:i:s')
                : null),
        ];
        array_filter($data);

        if (count($data)) {
            $this->mixPanel->people->set($user->getKey(), $data, $this->request->ip());
        }

        $this->mixPanel->track('User', [
            'Status' => 'Registered',
            '$ip' => $this->request->ip(),
        ]);
    }

    /**
     * @param Model $user
     */
    public function saving(Model $user)
    {
        $this->mixPanel->identify($user->getKey());
        $firstName = $user->getAttribute('first_name');
        $lastName = $user->getAttribute('last_name');

        if ($user->getAttribute('name')) {
            $nameParts = explode(' ', $user->getAttribute('name'));
            array_filter($nameParts);
            $lastName = array_pop($nameParts);
            $firstName = implode(' ', $nameParts);
        }

        $data = [
            '$first_name' => $firstName,
            '$last_name' => $lastName,
            '$name' => $user->getAttribute('name'),
            '$email' => $user->getAttribute('email'),
            '$created' => ($user->getAttribute('created_at')
                ? $user->getAttribute('created_at')->format('Y-m-d\Th:i:s')
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
        $this->mixPanel->track('User', [
            'Status' => 'Deactivated',
            '$ip' => $this->request->ip(),
        ]);
    }

    /**
     * @param Model $user
     */
    public function restored(Model $user)
    {
        $this->mixPanel->identify($user->getKey());
        $this->mixPanel->track('User', [
            'Status' => 'Reactivated',
            '$ip' => $this->request->ip(),
        ]);
    }
}
