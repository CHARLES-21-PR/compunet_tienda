<?php

return [
    // Stock thresholds used across the app (can be adjusted per deployment)
    'thresholds' => [
        // `low` — stock <= this value is considered low (but > 0)
        'low' => 5,
        // `mid` — stock <= this value (and > low) is considered medium
        'mid' => 10,
    ],

    // Colors used for dashboard widgets depending on stock state.
    'colors' => [
        'zero' => 'linear-gradient(90deg,#6b7280,#9ca3af)',
        'low'  => 'linear-gradient(90deg,#dc2626,#ef4444)',
        'mid'  => 'linear-gradient(90deg,#f59e0b,#d97706)',
        'ok'   => 'linear-gradient(90deg,#10b981,#059669)',
    ],
];
