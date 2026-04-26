<?php

use function Pest\Laravel\post;

it('throttles excessive registration attempts', function () {
    for ($i = 0; $i < 6; $i++) {
        $response = post('/register', [
            'name'     => 'Test User',
            'email'    => "test{$i}@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
    }
    expect($response->status())->toBe(429);
});
