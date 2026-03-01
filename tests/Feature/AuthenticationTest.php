<?php namespace GeneaLabs\LaravelMixpanel\Tests\Feature;

use GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\User;
use GeneaLabs\LaravelMixpanel\Tests\FeatureTestCase;
use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;

class AuthenticationTest extends FeatureTestCase
{
    use DatabaseMigrations;

    public function testLoginAttempt()
    {
        Event::fake([MixpanelEvent::class]);
        $user = User::factory()->create();

        $result = $this->visit('/login')
            ->type($user->email, 'email')
            ->type('hoogabaloo', 'password')
            ->press('Login');

        $this->assertResponseStatus(200);
        $result->seePageIs('/login');
        Event::assertDispatched(MixpanelEvent::class, function ($event) use ($user) {
            return ($event->user->email === $user->email && $event->names()->contains('Login Attempted'));
        });
    }

    public function testLoginSuccess()
    {
        Event::fake([MixpanelEvent::class]);
        $password = 'hoogabaloo';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $result = $this->visit('/login')
            ->type($user->email, 'email')
            ->type($password, 'password')
            ->press('Login');

        $this->assertResponseStatus(200);
        $result->seePageIs('/home');
        Event::assertDispatched(MixpanelEvent::class, function ($event) use ($user) {
            return ($event->user->email === $user->email && $event->names()->contains('User Logged In'));
        });
    }

    public function testLogoutSuccess()
    {
        Event::fake([MixpanelEvent::class]);
        $user = User::factory()->create();

        $result = $this->actingAs($user)
            ->post('/logout');

        $this->assertRedirectedTo('/');
        Event::assertDispatched(MixpanelEvent::class, function ($event) use ($user) {
            return ($event->user->email === $user->email && $event->names()->contains('User Logged Out'));
        });
    }
}
