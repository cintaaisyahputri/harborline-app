<?php

use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| HARBORLINE PROVISIONS. The actual application (JSON API) lives under
| /api -- see routes/api.php. This file serves two things:
|   1. the signed, unauthenticated printable receipt page
|   2. the React SPA shell for every other browser route, so a hard
|      refresh on e.g. /dashboard/orders still works (React Router
|      takes over client-side once the shell has loaded).
*/

// Printable "struk belanja" -- reached only via the signed print_url that
// GET /api/orders/{order}/receipt hands back (valid 30 minutes). No session
// login needed here on purpose: it's a link you open/print in a plain
// browser tab, not an authenticated page.
Route::get('/receipts/{order}/print', [ReceiptController::class, 'print'])
    ->name('receipts.print')
    ->middleware('signed');

// SPA catch-all. Must stay last. Anything under /api/* never reaches this
// file at all (it's registered separately in bootstrap/app.php), so this
// is safe to be this broad.
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*')->name('spa');

