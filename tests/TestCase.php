<?php

namespace GeneaLabs\LaravelMixpanel\Tests;

use GeneaLabs\LaravelMixpanel\Providers\Service;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            Service::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('mixpanel.token', '68dffdba4c272b791a2d4883b43ccfd7');
        $app['config']->set('mixpanel.enable-default-tracking', false);

        $app['config']->set('auth.providers.users.model', \GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\User::class);
    }

    protected function defineRoutes($router): void
    {
        $router->get('login', function () {
            return response('<html><body><form method="POST" action="/login"><input name="email"><input name="password" type="password"><button type="submit">Login</button></form></body></html>');
        })->name('login');

        $router->post('login', function (\Illuminate\Http\Request $request) {
            $credentials = $request->only('email', 'password');

            if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
                return redirect()->intended('/home');
            }

            return redirect()->back()->withInput($request->only('email'));
        })->name('login.post');

        $router->post('logout', function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Auth::logout();

            return redirect('/');
        })->name('logout');

        $router->get('home', function () {
            return response('Home');
        })->middleware('auth')->name('home');
    }
}
