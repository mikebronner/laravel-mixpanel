<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Test fixture routes that replicate the auth routes without requiring
| the laravel/ui package.
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes (replaces Auth::routes())
Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::post('login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        return redirect()->intended('/home');
    }

    return redirect()->back()->withInput($request->only('email'));
})->name('login.post');

Route::post('logout', function (Request $request) {
    Auth::logout();

    return redirect('/');
})->name('logout');

Route::get('register', function () {
    return view('auth.register');
})->name('register');

Route::post('register', function (Request $request) {
    $user = \App\User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    Auth::login($user);

    return redirect('/home');
})->name('register.post');

Route::get('password/reset', function () {
    return view('auth.passwords.email');
})->name('password.request');

Route::get('/home', 'HomeController@index')->name('home');
