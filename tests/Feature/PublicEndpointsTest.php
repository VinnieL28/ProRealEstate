<?php

use function Pest\Laravel\get;

it('serves the landing page', function () {
    get('/')->assertOk();
});

it('serves the terms page', function () {
    get('/terms')->assertOk();
});

it('serves the privacy page', function () {
    get('/privacy')->assertOk();
});
