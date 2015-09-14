<?php namespace GeneaLabs\LaravelMixpanel\Providers;

use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelEventHandler;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelUserObserver;
use Illuminate\Auth\Guard;
use Illuminate\Support\Facades\Event;
use Illuminate\HTTP\Request;
use Illuminate\Support\ServiceProvider;

class LaravelMixpanelServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(Guard $guard, LaravelMixpanel $mixPanel)
    {
        include __DIR__ . '/../Http/routes.php';

        $this->app->make(config('auth.model'))->observe(new LaravelMixpanelUserObserver($mixPanel));
        $eventHandler = new LaravelMixpanelEventHandler($guard, $mixPanel);

        Event::subscribe($eventHandler);
    }

    public function register()
    {
        $this->app->singleton(LaravelMixpanel::class, function () {
            return new LaravelMixpanel();
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return ['laravel-mixpanel'];
    }
}
