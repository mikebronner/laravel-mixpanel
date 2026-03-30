<?php

namespace GeneaLabs\LaravelMixpanel\Tests\Fixtures\App;

class UserWithMixpanelKey extends User
{
    protected $table = 'users';

    public function getMixpanelKey(): string
    {
        return 'custom-key-' . $this->getKey();
    }
}
