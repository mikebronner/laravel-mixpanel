<?php

namespace GeneaLabs\LaravelMixpanel\Tests\Unit;

use GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\MixpanelUserData;
use GeneaLabs\LaravelMixpanel\Tests\UnitTestCase;

class DataCallbackTest extends UnitTestCase
{
    public function testDataCallbackClassReturnsArray()
    {
        $data = (new MixpanelUserData)->process();

        $this->assertIsArray($data);
    }

    public function testDataCallbackArrayContainsValue()
    {
        $data = (new MixpanelUserData)->process();

        $this->assertArrayHasKey("test", $data);
        $this->assertEquals("value", $data["test"]);
    }
}
