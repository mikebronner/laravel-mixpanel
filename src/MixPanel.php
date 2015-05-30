<?php namespace GeneaLabs\MixPanel;

use Illuminate\Support\Facades\App;

class MixPanel extends \Mixpanel
{
    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct(config('services.mixpanel.token'), $options);
    }
}
