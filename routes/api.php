<?php

use GeneaLabs\LaravelMixpanel\Http\Controllers\StripeWebhooksController;

Route::post(
    'genealabs/laravel-mixpanel/stripe',
    StripeWebhooksController::class . '@postTransaction',
);
