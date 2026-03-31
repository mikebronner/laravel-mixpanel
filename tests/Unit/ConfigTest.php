<?php

namespace GeneaLabs\LaravelMixpanel\Tests\Unit;

use GeneaLabs\LaravelMixpanel\Tests\TestCase;
use ReflectionMethod;

class ConfigTest extends TestCase
{
    public function testConfigUsesNewMixpanelNamespace()
    {
        $this->assertNotNull(config('mixpanel.token'));
        $this->assertEquals('68dffdba4c272b791a2d4883b43ccfd7', config('mixpanel.token'));
    }

    public function testConfigHasAllExpectedKeys()
    {
        $this->assertNotNull(config('mixpanel.consumer'));
        $this->assertNotNull(config('mixpanel.connect-timeout'));
        $this->assertNotNull(config('mixpanel.timeout'));
        $this->assertTrue(config('mixpanel.enable-default-tracking') !== null);
    }

    public function testLegacyConfigFallback()
    {
        config(['mixpanel.token' => null]);
        config(['services.mixpanel' => [
            'token' => 'legacy-token-value',
        ]]);

        $provider = $this->app->getProvider(\GeneaLabs\LaravelMixpanel\Providers\Service::class);
        $method = new ReflectionMethod($provider, 'migrateDeprecatedConfig');
        $method->invoke($provider);

        $this->assertEquals('legacy-token-value', config('mixpanel.token'));
    }

    public function testMixpanelConfigPublishTag()
    {
        try {
            $this->artisan('vendor:publish', ['--tag' => 'mixpanel-config', '--force' => true]);
            $this->assertFileExists(config_path('mixpanel.php'));
        } finally {
            @unlink(config_path('mixpanel.php'));
        }
    }
}
