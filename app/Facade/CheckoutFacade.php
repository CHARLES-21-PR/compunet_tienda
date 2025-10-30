namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\NiubizPaymentService;
use App\Services\InvoiceService;
use App\Services\ShippingService;
use App\Services\InventoryService;

class Checkout extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'checkout';
    }
}

class CheckoutFacade
{
    protected $payment;
    protected $invoice;
    protected $shipping;
    protected $inventory;

    public function __construct(
        NiubizPaymentService $payment,
        InvoiceService $invoice,
        ShippingService $shipping,
        InventoryService $inventory
    ) {
        $this->payment = $payment;
        $this->invoice = $invoice;
        $this->shipping = $shipping;
        $this->inventory = $inventory;
    }

    public function procesarCompra($cliente, $productos, $monto, $tokenCliente)
    {
        if (!$this->payment->procesarPago($monto, $tokenCliente)) {
            throw new \Exception("Error al procesar el pago con Niubiz");
        }

        $factura = $this->invoice->generarFactura($cliente, $monto);
        $envio = $this->shipping->prepararEnvio((object)['id' => rand(1000, 9999)]);
        $inventario = $this->inventory->actualizarStock($productos);

        return [
            'factura' => $factura,
            'envio' => $envio,
            'inventario' => $inventario,
            'mensaje' => 'Compra completada exitosamente con Niubiz'
        ];
    }
}
