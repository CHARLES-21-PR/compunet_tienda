<?php

test('la aplicación responde en menos de 200ms', function () {
    $start = microtime(true);

    $response = $this->get('/up');

    $end = microtime(true);
    $duration = ($end - $start) * 1000;

    $response->assertStatus(200);
    
    expect($duration)->toBeLessThan(200);
});
