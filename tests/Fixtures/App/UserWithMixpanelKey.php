<?php

namespace GeneaLabs\LaravelMixpanel\Tests\Fixtures\App;

use GeneaLabs\LaravelMixpanel\Interfaces\HasCustomMixpanelKey;

class UserWithMixpanelKey extends User implements HasCustomMixpanelKey
{
    protected $table = 'users';

    public function getMixpanelKey(): string
    {
        return 'custom-key-' . $this->getKey();
    }
}
