<?php namespace GeneaLabs\MixPanel;

class MixPanel extends \Mixpanel
{
    protected static $instance;

    public function __construct($token, array $options = [])
    {
        parent::__construct($token, $options);
    }
}
