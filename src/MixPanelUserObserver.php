<?php namespace GeneaLabs\MixPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class MixPanelUserObserver
{
    protected $mixPanel;
    protected $request;

    /**
     * @param MixPanel $mixPanel
     */
    public function __construct(MixPanel $mixPanel, Request $request)
    {
        $this->mixPanel = $mixPanel;
        $this->request = $request;
    }

    /**
     * @param Model $user
     */
    public function created(Model $user)
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

        $this->mixPanel->alias($user->id);
        $this->mixPanel->people->set($user->id, $data, $this->request->ip);
        $this->mixPanel->track('User', ['Status' => 'Registered']);
    }

    /**
     * @param Model $user
     */
    public function saving(Model $user)
    {
        $this->mixPanel->identify($user->id);
        $data = [];

        if ($user->name) {
            $nameParts = explode(' ', $user->name);
            array_filter($nameParts);
            $lastName = array_pop($nameParts);
            $firstName = implode(' ', $nameParts);
            $user->first_name = $firstName;
            $user->last_name = $lastName;
        }

        $data[] = [
            '$first_name' => $user->first_name,
            '$last_name' => $user->last_name,
            '$email' => $user->email,
        ];

        if ($user->created_at) {
            $data[] = ['$created' => $user->created_at->format('Y-m-d\Th:i:s')];
        }

        array_filter($data);

        if (count($data)) {
            $this->mixPanel->people->set($user->id, $data, $this->request->ip);
        }
    }

    /**
     * @param Model $user
     */
    public function deleting(Model $user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->track('User', ['Status' => 'Deactivated']);
    }

    /**
     * @param Model $user
     */
    public function restored(Model $user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->track('User', ['Status' => 'Reactivated']);
    }
}
