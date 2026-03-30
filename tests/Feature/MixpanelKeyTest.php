<?php

namespace GeneaLabs\LaravelMixpanel\Tests\Feature;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;
use GeneaLabs\LaravelMixpanel\Listeners\MixpanelEvent as MixpanelEventListener;
use GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\User;
use GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\UserWithMixpanelKey;
use GeneaLabs\LaravelMixpanel\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class MixpanelKeyTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.mixpanel.enable-default-tracking' => true]);
    }

    public function testDefaultKeyUsesGetKey(): void
    {
        $user = User::factory()->create();

        $peopleMock = Mockery::mock();
        $peopleMock->shouldReceive('set')
            ->once()
            ->withArgs(function ($key) use ($user) {
                return $key === $user->getKey();
            });
        $peopleMock->shouldNotReceive('trackCharge');

        $mixpanelMock = Mockery::mock();
        $mixpanelMock->people = $peopleMock;
        $mixpanelMock->shouldReceive('identify')
            ->once()
            ->with($user->getKey());
        $mixpanelMock->shouldReceive('track')
            ->once();

        $this->app->instance('mixpanel', $mixpanelMock);

        $event = new MixpanelEvent($user, ['Test Event' => []]);
        (new MixpanelEventListener())->handle($event);
    }

    public function testCustomKeyUsesGetMixpanelKey(): void
    {
        config(['services.mixpanel.enable-default-tracking' => false]);
        $baseUser = User::factory()->create();
        config(['services.mixpanel.enable-default-tracking' => true]);

        $user = UserWithMixpanelKey::find($baseUser->id);
        $expectedKey = 'custom-key-' . $user->getKey();

        $peopleMock = Mockery::mock();
        $peopleMock->shouldReceive('set')
            ->once()
            ->withArgs(function ($key) use ($expectedKey) {
                return $key === $expectedKey;
            });
        $peopleMock->shouldNotReceive('trackCharge');

        $mixpanelMock = Mockery::mock();
        $mixpanelMock->people = $peopleMock;
        $mixpanelMock->shouldReceive('identify')
            ->once()
            ->with($expectedKey);
        $mixpanelMock->shouldReceive('track')
            ->once();

        $this->app->instance('mixpanel', $mixpanelMock);

        $event = new MixpanelEvent($user, ['Test Event' => []]);
        (new MixpanelEventListener())->handle($event);
    }

    public function testCustomKeyUsedForTrackCharge(): void
    {
        config(['services.mixpanel.enable-default-tracking' => false]);
        $baseUser = User::factory()->create();
        config(['services.mixpanel.enable-default-tracking' => true]);

        $user = UserWithMixpanelKey::find($baseUser->id);
        $expectedKey = 'custom-key-' . $user->getKey();

        $peopleMock = Mockery::mock();
        $peopleMock->shouldReceive('set')
            ->once()
            ->withArgs(function ($key) use ($expectedKey) {
                return $key === $expectedKey;
            });
        $peopleMock->shouldReceive('trackCharge')
            ->once()
            ->with($expectedKey, 2999);

        $mixpanelMock = Mockery::mock();
        $mixpanelMock->people = $peopleMock;
        $mixpanelMock->shouldReceive('identify')
            ->once()
            ->with($expectedKey);
        $mixpanelMock->shouldReceive('track')
            ->once();

        $this->app->instance('mixpanel', $mixpanelMock);

        $event = new MixpanelEvent($user, ['Purchase' => []], 2999);
        (new MixpanelEventListener())->handle($event);
    }

    public function testDefaultKeyUsedForTrackCharge(): void
    {
        $user = User::factory()->create();

        $peopleMock = Mockery::mock();
        $peopleMock->shouldReceive('set')
            ->once()
            ->withArgs(function ($key) use ($user) {
                return $key === $user->getKey();
            });
        $peopleMock->shouldReceive('trackCharge')
            ->once()
            ->with($user->getKey(), 500);

        $mixpanelMock = Mockery::mock();
        $mixpanelMock->people = $peopleMock;
        $mixpanelMock->shouldReceive('identify')
            ->once()
            ->with($user->getKey());
        $mixpanelMock->shouldReceive('track')
            ->once();

        $this->app->instance('mixpanel', $mixpanelMock);

        $event = new MixpanelEvent($user, ['Purchase' => []], 500);
        (new MixpanelEventListener())->handle($event);
    }

    public function testNoTrackingWhenDisabled(): void
    {
        config(['services.mixpanel.enable-default-tracking' => false]);

        $user = UserWithMixpanelKey::factory()->create();

        $mixpanelMock = Mockery::mock();
        $mixpanelMock->shouldNotReceive('identify');

        $this->app->instance('mixpanel', $mixpanelMock);

        $event = new MixpanelEvent($user, ['Test Event' => []]);
        (new MixpanelEventListener())->handle($event);
    }
}
