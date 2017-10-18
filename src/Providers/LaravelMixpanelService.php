<?php namespace GeneaLabs\LaravelMixpanel\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;

class LaravelMixpanelService extends MixpanelBaseService
{
    public function boot()
    {
        parent::boot();

        $this->initialize();
    }
}
