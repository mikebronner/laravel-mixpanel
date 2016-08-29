<?php namespace GeneaLabs\LaravelMixpanel\Http\ViewComposers;

use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
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

        if ($user) {
            app(LaravelMixpanel::class)->identify($user->getKey());
            app(LaravelMixpanel::class)->people->set($user->getKey(), [], request()->ip());
        }

        app(LaravelMixpanel::class)->track('Page View', ['Route' => $route]);
    }
}
