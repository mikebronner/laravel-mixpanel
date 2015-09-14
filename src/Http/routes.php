<?php

use GeneaLabs\MixPanel\Http\Controllers\StripeWebhooksController;
use Illuminate\Support\Facades\View;

Route::controller('genealabs/laravel-mixpanel/stripe', StripeWebhooksController::class);
