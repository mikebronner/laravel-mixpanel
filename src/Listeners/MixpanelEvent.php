<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Event;
use Illuminate\Support\Carbon;

class MixpanelEvent
{
    public function handle(Event $event)
    {
        $user = $event->user;

        if ($user && config("services.mixpanel.enable-default-tracking")) {

            $userKey = $user->getKey();

            if (method_exists($user, 'getMixPanelKey')) {
                $userKey = $user->getMixPanelKey();
            }

            $profileData = $this->getProfileData($user);
            $profileData = array_merge($profileData, $event->profileData);

            app('mixpanel')->identify($userKey);
            app('mixpanel')->people->set($userKey, $profileData, request()->ip());

            if ($event->charge !== 0) {
                app('mixpanel')->people->trackCharge($user->id, $event->charge);
            }

            foreach ($event->trackingData as $eventName => $data) {
                app('mixpanel')->track($eventName, $data);
            }
        }
    }

    private function getProfileData($user) : array
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
                ? (new Carbon())
                    ->parse($user->created_at)
                    ->format('Y-m-d\Th:i:s')
                : null),
        ];
        array_filter($data);

        return $data;
    }
}
