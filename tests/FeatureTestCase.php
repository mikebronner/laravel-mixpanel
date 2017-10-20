<?php namespace GeneaLabs\LaravelMixpanel\Tests;

use GeneaLabs\LaravelMixpanel\Providers\Service;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Laravel\BrowserKitTesting\TestCase;

abstract class FeatureTestCase extends TestCase
{
    use CreatesApplication;

    public $baseUrl = 'http://localhost';
}
