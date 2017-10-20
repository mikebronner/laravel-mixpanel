<?php namespace GeneaLabs\LaravelMixpanel\Tests;

use GeneaLabs\LaravelMixpanel\Providers\Service;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    public function createApplication()
    {
        $this->copyFixtures([
            __DIR__ . '/Fixtures/app/Http/Controllers/HomeController.php' => __DIR__ . '/../vendor/laravel/laravel/app/Http/Controllers/HomeController.php',
            __DIR__ . '/Fixtures/resources/views/home.blade.php' => __DIR__ . '/../vendor/laravel/laravel/resources/views/home.blade.php',
            __DIR__ . '/Fixtures/resources/views/layouts/app.blade.php' => __DIR__ . '/../vendor/laravel/laravel/resources/views/layouts/app.blade.php',
            __DIR__ . '/Fixtures/routes/web.php' => __DIR__ . '/../vendor/laravel/laravel/routes/web.php',
        ]);
        // $this->preLoadRoutes();
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $app->register(Service::class);

        return $app;
    }

    protected function copyFixtures(array $fixtures)
    {
        $fixtures = collect($fixtures)->each(function ($destination, $source) {
            $contents = file_get_contents($source);
            file_put_contents($destination, $contents);
        });
    }

    protected function preLoadRoutes()
    {
        $routes = file_get_contents(__DIR__ . '/../vendor/laravel/laravel/routes/web.php');
        $routes .= str_contains($routes, 'Auth::routes();') ? '' : "\nAuth::routes();\n";
        file_put_contents(__DIR__ . '/../vendor/laravel/laravel/routes/web.php', $routes);
    }
}
