<?php

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;
use GeneaLabs\LaravelMixpanel\Listeners\MixpanelEvent as MixpanelEventListener;
use GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\User;
use GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\UserWithMixpanelKey;

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
});

test('default key uses getKey', function () {
    $user = User::factory()->create();

    $peopleMock = Mockery::mock();
    $peopleMock->shouldReceive('set')
        ->once()
        ->withArgs(fn ($key) => $key === $user->getKey());
    $peopleMock->shouldNotReceive('trackCharge');

    $mixpanelMock = Mockery::mock();
    $mixpanelMock->people = $peopleMock;
    $mixpanelMock->shouldReceive('identify')
        ->once()
        ->with($user->getKey());
    $mixpanelMock->shouldReceive('track')
        ->once();

    $this->app->instance('mixpanel', $mixpanelMock);
    config(['services.mixpanel.enable-default-tracking' => true]);

    $event = new MixpanelEvent($user, ['Test Event' => []]);
    (new MixpanelEventListener())->handle($event);
});

test('custom key uses getMixpanelKey', function () {
    $baseUser = User::factory()->create();
    $user = UserWithMixpanelKey::find($baseUser->id);
    $expectedKey = 'custom-key-' . $user->getKey();

    $peopleMock = Mockery::mock();
    $peopleMock->shouldReceive('set')
        ->once()
        ->withArgs(fn ($key) => $key === $expectedKey);
    $peopleMock->shouldNotReceive('trackCharge');

    $mixpanelMock = Mockery::mock();
    $mixpanelMock->people = $peopleMock;
    $mixpanelMock->shouldReceive('identify')
        ->once()
        ->with($expectedKey);
    $mixpanelMock->shouldReceive('track')
        ->once();

    $this->app->instance('mixpanel', $mixpanelMock);
    config(['services.mixpanel.enable-default-tracking' => true]);

    $event = new MixpanelEvent($user, ['Test Event' => []]);
    (new MixpanelEventListener())->handle($event);
});

test('custom key used for trackCharge', function () {
    $baseUser = User::factory()->create();
    $user = UserWithMixpanelKey::find($baseUser->id);
    $expectedKey = 'custom-key-' . $user->getKey();

    $peopleMock = Mockery::mock();
    $peopleMock->shouldReceive('set')
        ->once()
        ->withArgs(fn ($key) => $key === $expectedKey);
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
    config(['services.mixpanel.enable-default-tracking' => true]);

    $event = new MixpanelEvent($user, ['Purchase' => []], 2999);
    (new MixpanelEventListener())->handle($event);
});

test('default key used for trackCharge', function () {
    $user = User::factory()->create();

    $peopleMock = Mockery::mock();
    $peopleMock->shouldReceive('set')
        ->once()
        ->withArgs(fn ($key) => $key === $user->getKey());
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
    config(['services.mixpanel.enable-default-tracking' => true]);

    $event = new MixpanelEvent($user, ['Purchase' => []], 500);
    (new MixpanelEventListener())->handle($event);
});

test('no tracking when disabled', function () {
    $user = User::factory()->create();

    $mixpanelMock = Mockery::mock();
    $mixpanelMock->shouldNotReceive('identify');

    $this->app->instance('mixpanel', $mixpanelMock);

    $event = new MixpanelEvent($user, ['Test Event' => []]);
    (new MixpanelEventListener())->handle($event);
});
