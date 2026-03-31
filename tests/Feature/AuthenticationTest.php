<?php

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;
use GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    config(['mixpanel.enable-default-tracking' => true]);
});

test('login attempt dispatches mixpanel event', function () {
    Event::fake([MixpanelEvent::class]);
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    config(['mixpanel.enable-default-tracking' => true]);
    $response->assertStatus(302);
    $response->assertRedirect();
    Event::assertDispatched(MixpanelEvent::class, function ($event) use ($user) {
        return $event->user->email === $user->email && $event->names()->contains('Login Attempted');
    });
});

test('login success dispatches mixpanel event', function () {
    Event::fake([MixpanelEvent::class]);
    $password = 'hoogabaloo';
    $user = User::factory()->create([
        'password' => bcrypt($password),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => $password,
    ]);

    $response->assertRedirect('/home');
    Event::assertDispatched(MixpanelEvent::class, function ($event) use ($user) {
        return $event->user->email === $user->email && $event->names()->contains('User Logged In');
    });
});

test('logout success dispatches mixpanel event', function () {
    Event::fake([MixpanelEvent::class]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/logout');

    $response->assertRedirect('/');
    Event::assertDispatched(MixpanelEvent::class, function ($event) use ($user) {
        return $event->user->email === $user->email && $event->names()->contains('User Logged Out');
    });
});
