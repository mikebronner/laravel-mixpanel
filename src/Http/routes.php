<?php

use GeneaLabs\LaravelMixpanel\Http\Controllers\StripeWebhooksController;
use Illuminate\Support\Facades\View;

Route::controller('genealabs/laravel-mixpanel/stripe', StripeWebhooksController::class);
