<?php

use App\Http\Controllers\Offering\OfferingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/offerings', [OfferingController::class, 'apiIndex'])
        ->name('api.offerings.index');

    Route::post('/offerings', [OfferingController::class, 'apiStore'])
        ->name('api.offerings.store');

    Route::patch('/offerings/{off}', [OfferingController::class, 'apiUpdate'])
        ->name('api.offerings.update');
});
