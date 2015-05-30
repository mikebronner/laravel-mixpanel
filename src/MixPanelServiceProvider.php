<?php namespace GeneaLabs\MixPanel;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class MixPanelServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->app->make(config('auth.model'))->observe(new MixPanelUserObserver($this->app->make(MixPanel::class)));
        $eventHandler = new MixPanelUserEventHandler();

        Event::subscribe($eventHandler);
    }

    public function register()
    {
        $this->app->singleton(MixPanel::class, function () {
            return new MixPanel();
        });
    }

    public function provides()
    {
        return ['mixpanel'];
    }
}
