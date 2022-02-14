<?php namespace GeneaLabs\LaravelMixpanel\Providers;

use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use GeneaLabs\LaravelMixpanel\Listeners\Login as LoginListener;
use GeneaLabs\LaravelMixpanel\Listeners\LoginAttempt;
use GeneaLabs\LaravelMixpanel\Listeners\Logout as LogoutListener;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelUserObserver;
use GeneaLabs\LaravelMixpanel\Console\Commands\Publish;
use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;
use GeneaLabs\LaravelMixpanel\Listeners\MixpanelEvent as MixpanelEventListener;
use Illuminate\Contracts\View\View;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\HTTP\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class Service extends EventServiceProvider
{
    protected $defer = false;
    protected $listen = [
        MixpanelEvent::class => [MixpanelEventListener::class],
        Attempting::class => [LoginAttempt::class],
        Login::class => [LoginListener::class],
        Logout::class => [LogoutListener::class],
    ];

    public function boot()
    {
        parent::boot();

        include __DIR__ . '/../../routes/api.php';

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'genealabs-laravel-mixpanel');
        $this->publishes([
            __DIR__ . '/../../public' => public_path(),
        ], 'assets');

        if (config('services.mixpanel.enable-default-tracking')) {
            $authModel = config('auth.providers.users.model') ?? config('auth.model');
            $this->app->make($authModel)->observe(new LaravelMixpanelUserObserver());
        }
    }

    public function register()
    {
        parent::register();
        
        $this->mergeConfigFrom(__DIR__ . '/../../config/services.php', 'services');
        $this->commands(Publish::class);
        $this->app->singleton('mixpanel', LaravelMixpanel::class);
    }

    public function provides() : array
    {
        return ['genealabs-laravel-mixpanel'];
    }
}
