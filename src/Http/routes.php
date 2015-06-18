<?php

use GeneaLabs\MixPanel\Http\Controllers\StripeWebhooksController;
use Illuminate\Support\Facades\View;

Route::controller('mixpanel/webhooks/stripe', StripeWebhooksController::class);
