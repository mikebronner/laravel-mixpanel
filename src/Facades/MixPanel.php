<?php namespace GeneaMatic\GeneaLabs\MixPanel\Facades;

use Illuminate\Support\Facades\Facade;

class MixPanel extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'mixpanel';
    }
}
