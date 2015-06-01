<?php namespace GeneaLabs\MixPanel;

use Illuminate\Auth\Guard;
use Illuminate\Support\Facades\Event;
use Illuminate\HTTP\Request;
use Illuminate\Support\ServiceProvider;

class MixPanelServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(Guard $guard, MixPanel $mixPanel, Request $request)
    {
        include __DIR__ . '/HTTP/routes.php';

        $this->app->make(config('auth.model'))->observe(new MixPanelUserObserver($mixPanel, $request));
        $eventHandler = new MixPanelUserEventHandler($guard, $mixPanel, $request);

        Event::subscribe($eventHandler);
    }

    public function register()
    {
        $this->app->singleton(MixPanel::class, function () {
            return new MixPanel();
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return ['mixpanel'];
    }
}
