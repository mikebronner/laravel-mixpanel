<?php namespace GeneaLabs\LaravelMixpanel\Tests\Feature;

use App\User;
use GeneaLabs\LaravelMixpanel\Facades\Mixpanel;
use GeneaLabs\LaravelMixpanel\Tests\FeatureTestCase;
use GeneaLabs\LaravelMixpanel\Listeners\LoginAttempt;
use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent;
use GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\UserWithMixKey;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Mockery;

class AuthenticationTest extends FeatureTestCase
{
    use DatabaseMigrations;

    public function testLoginAttempt()
    {
        Event::fake([MixpanelEvent::class]);
        $user = factory(User::class)->create();

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
        $user = factory(User::class)->create([
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
        $user = factory(User::class)->create();

        $result = $this->actingAs($user)
            ->post('/logout');

        $this->assertRedirectedTo('/');
        Event::assertDispatched(MixpanelEvent::class, function ($event) use ($user) {
            return ($event->user->email === $user->email && $event->names()->contains('User Logged Out'));
        });
    }

    public function testUsingAlternateKey()
    {
        $spy = Mixpanel::spy();
        $spy->people = new \Producers_MixpanelPeople([]);

        $user = UserWithMixKey::create([
            'name'     => 'Test User',
            'email'    => 'tester@testing.com',
            'password' => bcrypt('password'),
        ]);

        event(new MixpanelEvent($user, ['User Logged In' => []]));

        $spy->shouldHaveReceived('identify')->with('random-other-identifier');
    }

    public function testUsingDefaultKey()
    {
        $spy = Mixpanel::spy();
        $spy->people = new \Producers_MixpanelPeople([]);

        $user = factory(User::class)->create();

        event(new MixpanelEvent($user, ['User Logged In' => []]));

        $spy->shouldHaveReceived('identify')->with($user->id);
    }
}
