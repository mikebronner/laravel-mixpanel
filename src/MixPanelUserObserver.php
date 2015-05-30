<?php namespace GeneaLabs\MixPanel;

class MixPanelUserObserver
{
    protected $mixPanel;

    public function __construct(MixPanel $mixPanel)
    {
        $this->mixPanel = $mixPanel;
    }

    public function created($user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->people->set($user->id, [
            '$first_name' => $user->first_name,
            '$last_name' => $user->last_name,
            '$email' => $user->email,
            '$created' => $user->created_at,
        ]);
        $this->mixPanel->track('User Registered');
    }

    public function saving($user)
    {
        $this->mixPanel->identify($user->id);

        if ($user->getAttribute('stripe_active') && ! $user->getOriginal('stripe_active')) {
            $this->mixPanel->track('User Subscribed');
        }

        if (! $user->getAttribute('stripe_active') && $user->getOriginal('stripe_active')) {
            $this->mixPanel->track('User Unsubscribed');
        }

        if ($user->getAttribute('last_four') && ! $user->getOriginal('last_four')) {
            $this->mixPanel->track('User Entered Payment Information');
        }

        if ($user->getAttribute('stripe_plan') && ! $user->getOriginal('stripe_plan')) {
            $this->mixPanel->people->set($user->id, [
                'subscription' => $user->stripe_plan,
            ]);
            $this->mixPanel->track('User Changed Subscription Plan');
        }
    }

    public function deleting($user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->track('User Deactivated');
    }

    public function restored($user)
    {
        $this->mixPanel->identify($user->id);
        $this->mixPanel->track('User Reactivated');
    }
}
