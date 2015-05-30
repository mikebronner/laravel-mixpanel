<?php namespace GeneaLabs\MixPanel;

use GeneaMatic\User;
use Illuminate\Support\Facades\App;

class MixPanel extends \Mixpanel
{
    public function __construct(array $options = [])
    {
        parent::__construct(config('services.mixpanel.token'), $options);
    }
}
