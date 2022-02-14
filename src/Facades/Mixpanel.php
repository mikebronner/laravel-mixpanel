<?php namespace GeneaLabs\LaravelMixpanel\Facades;

use Illuminate\Support\Facades\Facade;

class Mixpanel extends Facade
{
    protected static function getFacadeAccessor() : string
    {
        return 'mixpanel';
    }
}
