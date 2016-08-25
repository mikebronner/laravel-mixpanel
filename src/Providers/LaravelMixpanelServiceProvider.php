<?php namespace GeneaLabs\LaravelMixpanel\Providers;

use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelEventHandler;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelUserObserver;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Event;
use Illuminate\HTTP\Request;
use Illuminate\Support\ServiceProvider;

class LaravelMixpanelServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(Request $request, Guard $guard, LaravelMixpanel $mixPanel)
    {
        include __DIR__ . '/../../../routes/api.php';

        if (config('services.mixpanel.enable-default-tracking')) {
            $this->app->make(config('auth.model'))->observe(new LaravelMixpanelUserObserver($request, $mixPanel));
            $eventHandler = new LaravelMixpanelEventHandler($request, $guard, $mixPanel);

            Event::subscribe($eventHandler);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/services.php', 'services');

        $this->app->singleton(LaravelMixpanel::class);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return ['laravel-mixpanel'];
    }
}
