<?php

namespace GeneaLabs\LaravelMixpanel\Tests\Fixtures\App;

use GeneaLabs\LaravelMixpanel\Interfaces\DataCallback;

class MixpanelUserData implements DataCallback
{
    public function process(array $data = []) : array
    {
        $data["test"] = "value";

        return $data;
    }
}
