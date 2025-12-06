<?php

test('la aplicación responde en menos de 200ms', function () {
    $start = microtime(true);

    $response = $this->get('/up');

    $end = microtime(true);
    $duration = ($end - $start) * 1000; // Convertir a milisegundos

    $response->assertStatus(200);
    
    // Asegurar que el tiempo de respuesta sea menor a 200ms
    // Nota: En entornos locales o de CI lentos, esto podría ser flaky.
    // Se recomienda usar un umbral más alto para CI o herramientas dedicadas como k6.
    expect($duration)->toBeLessThan(200);
});
