<?php namespace GeneaLabs\MixPanel;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class MixPanelServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        include __DIR__ . '/HTTP/routes.php';

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

    /**
     * @return array
     */
    public function provides()
    {
        return ['mixpanel'];
    }
}
