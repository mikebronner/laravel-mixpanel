<?php namespace GeneaLabs\LaravelMixpanel;

use Illuminate\Support\Facades\App;

class LaravelMixpanel extends \Mixpanel
{
    private $defaults = [
        'consumer' => 'socket',
        'connect_timeout' => 2,
        'timeout' => 2,
    ];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options = array_merge($this->defaults, $options);

        parent::__construct(config('services.mixpanel.token'), $options);
    }
}
