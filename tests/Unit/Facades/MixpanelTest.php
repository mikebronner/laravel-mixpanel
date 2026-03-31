<?php

use GeneaLabs\LaravelMixpanel\Facades\Mixpanel;
use GeneaLabs\LaravelMixpanel\LaravelMixpanel;

test('facade can be referenced', function () {
    expect(Mixpanel::getFacadeRoot())->toBeInstanceOf(LaravelMixpanel::class);
});
