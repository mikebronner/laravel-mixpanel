<?php namespace GeneaLabs\LaravelMixpanel\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;

if (starts_with(app()->version(), '5.1.')) {
    class LaravelMixpanelService extends MixpanelBaseService
    {
        public function boot(DispatcherContract $events)
        {
            parent::boot($events);

            $this->initialize();
        }
    }
} else {
    class LaravelMixpanelService extends MixpanelBaseService
    {
        public function boot()
        {
            parent::boot();

            $this->initialize();
        }
    }
}
