<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

//  応用要件：/api/v1/contacts のURL体系に対応させます
Route::prefix('v1')->group(function () {

    // お問い合わせAPIの一連のCRUDルートを一括定義
    Route::apiResource('contacts', ContactApiController::class)->names([
        'index' => 'api.v1.contacts.index',
        'show' => 'api.v1.contacts.show',
        'store' => 'api.v1.contacts.store',
        'update' => 'api.v1.contacts.update',
        'destroy' => 'api.v1.contacts.destroy',
    ]);

});