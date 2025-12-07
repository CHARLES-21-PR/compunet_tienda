<?php

test('el endpoint de health check /up responde correctamente', function () {
    $response = $this->get('/up');

    $response->assertStatus(200);
});
