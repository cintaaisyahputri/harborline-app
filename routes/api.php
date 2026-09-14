<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FleetController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ComplianceController;
use App\Http\Controllers\Api\WarehouseController;

/*
|--------------------------------------------------------------------------
| API Routes -- HARBORLINE PROVISIONS
|--------------------------------------------------------------------------
| Prefix: /api
| Auth:   Laravel Sanctum (Bearer token)
| Roles:  admin, fleet_manager, warehouse, buyer  (admin always passes 'role:' checks)
*/

// ---- Public ----
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');
// Step 2 of login: exchange the emailed 6-digit code for a Sanctum token.
// A tighter, separate limiter here specifically blocks OTP brute-forcing
// (only 1,000,000 possible 6-digit codes -- without this, someone could
// script through them well within the 10-minute expiry window).
Route::post('/login/verify', [AuthController::class, 'verifyLogin'])
    ->middleware('throttle:10,1');

// ---- Protected (auth:sanctum) ----
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // FLEET -- vessels. Reads open to any authenticated role; writes gated
    // to fleet_manager (admin always passes) via each Form Request's authorize().
    Route::apiResource('fleet', FleetController::class)
        ->parameters(['fleet' => 'vessel']);
    Route::patch('/fleet/{vessel}/position', [FleetController::class, 'updatePosition'])
        ->middleware('role:fleet_manager');

    // WAREHOUSES
    Route::apiResource('warehouses', WarehouseController::class);

    // INVENTORY -- reads open to any authenticated role; writes gated to
    // warehouse staff (admin always passes) via each Form Request's authorize().
    Route::apiResource('inventory', InventoryController::class);

    // ORDERS -- any authenticated user can browse/create; status transitions
    // (fulfil/cancel) are restricted to warehouse staff inside the controller.
    Route::apiResource('orders', OrderController::class);

    // RECEIPT -- itemised JSON struk for a confirmed/fulfilled order, plus a
    // short-lived signed link to the printable HTML version (routes/web.php).
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt']);

    // COMPLIANCE / CERTIFICATES
    Route::apiResource('compliance', ComplianceController::class)
        ->parameters(['compliance' => 'certificate']);
});

// Any unmatched /api/* call falls through to the JSON 404 handler
// registered in bootstrap/app.php.
