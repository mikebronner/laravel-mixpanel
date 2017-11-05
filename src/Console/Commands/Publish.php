<?php namespace GeneaLabs\LaravelMixpanel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use GeneaLabs\LaravelMixpanel\Providers\Service;

class Publish extends Command
{
    protected $signature = 'mixpanel:publish {--assets}';
    protected $description = 'Publish various assets of the mixpanel package.';

    public function handle()
    {
        if ($this->option('assets')) {
            $this->call('vendor:publish', [
                '--provider' => Service::class,
                '--tag' => ['assets'],
                '--force' => true,
            ]);
        }
    }
}
