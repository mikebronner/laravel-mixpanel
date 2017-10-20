<?php namespace GeneaLabs\LaravelMixpanel\Tests\Feature;

use App\User;
use GeneaLabs\LaravelMixpanel\Tests\FeatureTestCase;
use GeneaLabs\LaravelMixpanel\Listeners\Attempt;
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
        $this->expectsEvents(Attempting::class);
        $listener = Mockery::spy(Attempt::class);
        app()->instance(Attempt::class, $listener);

        $result = $this->visit('/login')
            ->type('test@noemail.com', 'email')
            ->type('hoogabaloo', 'password')
            ->press('Login');

        $this->assertResponseStatus(200);
        $result->seePageIs('/login');
        $listener->shouldHaveReceived('handle');
    }

    public function testLoginSuccess()
    {
        $password = 'hoogabaloo';
        $user = factory(User::class)->create([
            'password' => bcrypt($password),
        ]);
        $this->expectsEvents(Login::class);
        // $listener = Mockery::spy(LaravelMixpanelEventHandler::class);
        // app()->instance(LaravelMixpanelEventHandler::class, $listener);

        $result = $this->visit('/login')
            ->type($user->email, 'email')
            ->type($password, 'password')
            ->press('Login');

        $this->assertResponseStatus(200);
        // $listener->shouldHaveReceived('handle');
        $result->seePageIs('/home');
    }

    public function testLogoutSuccess()
    {
        $user = factory(User::class)->create();
        $this->expectsEvents(Logout::class);
        // $listener = Mockery::spy(LaravelMixpanelEventHandler::class);
        // app()->instance(LaravelMixpanelEventHandler::class, $listener);

        $this->actingAs($user)
            ->post('/logout');

        // $this->assertResponseStatus(200);
        // $listener->shouldHaveReceived('handle');
        $this->assertRedirectedTo('/');
    }
}
