<?php

namespace GeneaLabs\LaravelMixpanel\Tests\Feature;

use GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\User;
use GeneaLabs\LaravelMixpanel\Tests\TestCase;
use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['mixpanel.enable-default-tracking' => true]);
    }

    public function testLoginAttempt()
    {
        Event::fake([MixpanelEvent::class]);
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect();
        Event::assertDispatched(MixpanelEvent::class, function ($event) use ($user) {
            return $event->user->email === $user->email && $event->names()->contains('Login Attempted');
        });
    }

    public function testLoginSuccess()
    {
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
    }

    public function testLogoutSuccess()
    {
        Event::fake([MixpanelEvent::class]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/logout');

        $response->assertRedirect('/');
        Event::assertDispatched(MixpanelEvent::class, function ($event) use ($user) {
            return $event->user->email === $user->email && $event->names()->contains('User Logged Out');
        });
    }
}
