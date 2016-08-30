<?php namespace GeneaLabs\LaravelMixpanel\Providers;

use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelEventHandler;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelUserObserver;
use GeneaLabs\LaravelMixpanel\Console\Commands\Publish;
use GeneaLabs\LaravelMixpanel\Http\ViewComposers\AllViews;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\View\View;
use Illuminate\HTTP\Request;
use Illuminate\Support\ServiceProvider;

class LaravelMixpanelService extends ServiceProvider
{
    protected $defer = false;

    public function boot(Request $request, Guard $guard, LaravelMixpanel $mixPanel)
    {
        include __DIR__ . '/../../routes/api.php';

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'genealabs-laravel-mixpanel');
        $this->publishes([
            __DIR__ . '/../../public' => public_path(),
        ], 'assets');

        if (config('services.mixpanel.enable-default-tracking')) {
            $authModel = config('auth.providers.users.model') ?? config('auth.model');
            $this->app->make($authModel)->observe(new LaravelMixpanelUserObserver());
            app('events')->subscribe(new LaravelMixpanelEventHandler($guard));
            view()->composer('*', AllViews::class);
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
