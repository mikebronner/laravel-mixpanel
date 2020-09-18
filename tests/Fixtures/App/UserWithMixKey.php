<?php namespace GeneaLabs\LaravelMixpanel\Tests\Fixtures\App;

class UserWithMixKey extends User
{
    protected $table = 'users';

    public function getMixPanelKey()
    {
        return 'random-other-identifier';
    }
}
