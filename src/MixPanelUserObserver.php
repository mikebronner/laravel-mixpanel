<?php namespace GeneaLabs\MixPanel;

use Illuminate\Database\Eloquent\Model;

class MixPanelUserObserver
{
    protected $mixPanel;

    /**
     * @param MixPanel $mixPanel
     */
    public function __construct(MixPanel $mixPanel)
    {
        $this->mixPanel = $mixPanel;
    }

    /**
     * @param Model $user
     */
    public function created(Model $user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->people->set($user->id, [
            '$first_name' => $user->first_name,
            '$last_name' => $user->last_name,
            '$email' => $user->email,
            '$created' => $user->created_at->format('Y-m-d\Th:i:s'),
        ]);
        $this->mixPanel->track('User Registered');
    }

    /**
     * @param Model $user
     */
    public function saving(Model $user)
    {
        $this->mixPanel->identify($user->id);
        $data = [];

        if ($user->stripe_active && ! $user->getOriginal('stripe_active')) {
            $this->mixPanel->track('Subscription Succeeded');
        }

        if (! $user->stripe_active && $user->getOriginal('stripe_active')) {
            $this->mixPanel->track('Subscription Cancelled');
        }

        if ($user->last_four && ! $user->getOriginal('last_four')) {
            $this->mixPanel->track('Payment Information Entered');
        }

        if ($user->stripe_plan && ! $user->getOriginal('stripe_plan')) {
            $data[] = ['subscription' => $user->stripe_plan];
            $this->mixPanel->track('Subscription Plan Changed');
        }

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
            '$created' => $user->created_at->format('Y-m-d\Th:i:s'),
        ];

        array_filter($data);

        if (count($data)) {
            $this->mixPanel->people->set($user->id, $data);
        }
    }

    /**
     * @param Model $user
     */
    public function deleting(Model $user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->track('User Deactivated');
    }

    /**
     * @param Model $user
     */
    public function restored(Model $user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->track('User Reactivated');
    }
}
