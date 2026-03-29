<?php

namespace GeneaLabs\LaravelMixpanel\Tests\Unit\Facades;

use GeneaLabs\LaravelMixpanel\Facades\Mixpanel;
use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use GeneaLabs\LaravelMixpanel\Tests\TestCase;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class MixpanelTest extends TestCase
{
    public function testFacadeCanBeReferenced()
    {
        $instance = Mixpanel::getFacadeRoot();

        $this->assertInstanceOf(LaravelMixpanel::class, $instance);
    }
}
