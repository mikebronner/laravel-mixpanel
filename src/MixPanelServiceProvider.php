<?php namespace GeneaMatic\GeneaLabs\MixPanel;

use Illuminate\Support\ServiceProvider;

class MixPanelServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        $this->app->singleton(MixPanel::class, function () {
            return MixPanel::getInstance(config('services.mixpanel.token'));
        });
    }

    public function provides()
    {
        return ['mixpanel'];
    }
}
