<?php namespace GeneaLabs\LaravelMixpanel\Providers;

use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelEventHandler;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelUserObserver;
use GeneaLabs\LaravelMixpanel\Console\Commands\Publish;
use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;
use GeneaLabs\LaravelMixpanel\Listeners\MixpanelEvent as MixpanelEventListener;
use Illuminate\Contracts\View\View;
use Illuminate\HTTP\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

abstract class Service extends EventServiceProvider
{
    protected $defer = false;
    protected $listen = [
        MixpanelEvent::class => [
            MixpanelEventListener::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        $this->initialize();
    }

    protected function initialize()
    {
        include __DIR__ . '/../../routes/api.php';

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'genealabs-laravel-mixpanel');
        $this->publishes([
            __DIR__ . '/../../public' => public_path(),
        ], 'assets');

        if (config('services.mixpanel.enable-default-tracking')) {
            $authModel = config('auth.providers.users.model') ?? config('auth.model');
            $this->app->make($authModel)->observe(new LaravelMixpanelUserObserver());
            app('events')->subscribe(new LaravelMixpanelEventHandler());
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/services.php', 'services');
        $this->commands(Publish::class);
        $this->app->singleton('mixpanel', LaravelMixpanel::class);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return ['genealabs-laravel-mixpanel'];
    }
}
