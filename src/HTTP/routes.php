<?php

use GeneaLabs\MixPanel\HTTP\Controllers\StripeWebhooksController;
use Illuminate\Support\Facades\View;

Route::controller('mixpanel/webhooks/stripe', StripeWebhooksController::class);
