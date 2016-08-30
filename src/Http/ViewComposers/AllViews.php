<?php namespace GeneaLabs\LaravelMixpanel\Http\ViewComposers;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;
use Illuminate\View\View;

class AllViews
{
    public function compose($view)
    {
        $route = '';

        if (starts_with(app()->version(), '5.1.')) {
            $user = auth()->user();
            $route = $view->uri;
        }

        if (starts_with(app()->version(), '5.3.')) {
            $user = auth()->user();
            $route = $view->view;
        }

        $trackingData = [
            ['Page View', ['Route' => $route]],
        ];
        event(new MixpanelEvent($user, $trackingData));
    }
}
