<?php

test('la aplicación envía cabeceras de seguridad HTTP correctas', function () {
    $response = $this->get('/');

    $response->assertStatus(200);

    $response->assertHeader('X-Frame-Options');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');

    $cookies = $response->headers->getCookies();
    $sessionCookie = null;

    foreach ($cookies as $cookie) {
        if ($cookie->getName() === config('session.cookie', 'laravel_session')) {
            $sessionCookie = $cookie;
            break;
        }
    }

    if ($sessionCookie) {
        expect($sessionCookie->isHttpOnly())->toBeTrue();
        
        if (config('session.secure')) {
             expect($sessionCookie->isSecure())->toBeTrue();
        }
    }
});
