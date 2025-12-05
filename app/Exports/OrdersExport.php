<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Descargar todos los pedidos con la información del usuario
        return Order::with('user')->get();
    }

    /**
     * Definir los títulos de las columnas (Cabecera)
     */
    public function headings(): array
    {
        return [
            'ID Pedido',
            'Cliente',
            'Email',
            'Total (S/)',
            'Estado',
            'Fecha de Creación',
        ];
    }

    /**
     * Mapear los datos de cada fila
     */
    public function map($order): array
    {
        return [
            $order->id,
            $order->user ? $order->user->name : 'Invitado', // Nombre del cliente
            $order->user ? $order->user->email : 'N/A',     // Email
            $order->total_amount,                           // Total
            $order->status,                                 // Estado (ej. pending, paid)
            $order->created_at->format('d/m/Y H:i'),        // Fecha formateada
        ];
    }
}