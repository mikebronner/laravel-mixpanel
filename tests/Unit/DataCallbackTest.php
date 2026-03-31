<?php

use GeneaLabs\LaravelMixpanel\Tests\Fixtures\App\MixpanelUserData;

test('data callback class returns array', function () {
    $data = (new MixpanelUserData)->process();

    expect($data)->toBeArray();
});

test('data callback array contains value', function () {
    $data = (new MixpanelUserData)->process();

    expect($data)
        ->toHaveKey('test')
        ->and($data['test'])->toBe('value');
});
