<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use Carbon\Carbon;
use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Event;

class MixpanelEvent
{
    public function handle(Event $event)
    {
        $user = $event->user;

        if ($user) {
            $profileData = $this->getProfileData($user);
            $profileData = array_merge($profileData, $event->profileData);

            app('mixpanel')->identify($user->getKey());
            app('mixpanel')->people->set($user->getKey(), $profileData, request()->ip());

            if ($event->charge !== 0) {
                app('mixpanel')->people->trackCharge($user->id, $event->charge);
            }

            array_map(function ($data) {
                app('mixpanel')->track($data);
            }, $event->trackingData);
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
                ? (new Carbon)->parse($user->created_at)->format('Y-m-d\Th:i:s')
                : null),
        ];
        array_filter($data);

        return $data;
    }
}
