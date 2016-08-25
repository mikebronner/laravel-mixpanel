<?php

use GeneaLabs\LaravelMixpanel\Http\Controllers\StripeWebhooksController;
use Illuminate\Support\Facades\View;

Route::post('genealabs/laravel-mixpanel/stripe', ['uses' => 'GeneaLabs\LaravelMixpanel\Http\Controllers\StripeWebhooksController@postTransaction']);
