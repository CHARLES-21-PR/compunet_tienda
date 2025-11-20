<?php

return [
    // Definición centralizada de estados de pedido para usar en vistas y controladores
    'statuses' => [
        'paid' => [
            'label' => 'Pagado',
            'bg' => 'bg-green-50',
            'text' => 'text-green-800',
            'border' => 'border-green-500',
            'badge' => 'success'
        ],
        'delivered' => [
            'label' => 'Entregado',
            'bg' => 'bg-indigo-50',
            'text' => 'text-indigo-800',
            'border' => 'border-indigo-500',
            'badge' => 'primary'
        ],
        'cancelled' => [
            'label' => 'Cancelado',
            'bg' => 'bg-red-50',
            'text' => 'text-red-800',
            'border' => 'border-red-500',
            'badge' => 'danger'
        ],
        'failed' => [
            'label' => 'Fallido',
            'bg' => 'bg-red-50',
            'text' => 'text-red-800',
            'border' => 'border-red-500',
            'badge' => 'warning'
        ],
        'pending' => [
            'label' => 'Pendiente',
            'bg' => 'bg-yellow-50',
            'text' => 'text-yellow-800',
            'border' => 'border-yellow-400',
            'badge' => 'warning'
        ],
        'shipped' => [
            'label' => 'Enviado',
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-800',
            'border' => 'border-blue-500',
            'badge' => 'primary'
        ],
    ],
];
