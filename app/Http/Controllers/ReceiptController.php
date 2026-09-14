<?php

namespace App\Http\Controllers;

use App\Models\Order;

class ReceiptController extends Controller
{
    /**
     * GET /receipts/{order}/print  (signed URL only, see routes/web.php)
     * Renders a plain, print-ready HTML "struk belanja" for the order.
     * No auth guard here -- the request only ever gets this far if the
     * signature (minted by OrderController::receipt) is valid and unexpired.
     */
    public function print(Order $order)
    {
        abort_if($order->status === 'cancelled', 404);

        $order->load(['buyer:id,name,email', 'warehouse:id,name,location', 'items.inventoryItem:id,name,sku']);

        return view('receipts.print', ['order' => $order]);
    }
}
