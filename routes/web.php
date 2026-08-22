<?php

use App\Http\Controllers\Admin\SystemTagController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Communication\CommunicationController;
use App\Http\Controllers\Investment\ExpenseController;
use App\Http\Controllers\Knowledge\KnowledgeItemController;
use App\Http\Controllers\Offering\OfferingController;
use App\Http\Controllers\Purchase\PurchaseController;
use App\Http\Controllers\Relationship\RelationshipController;
use App\Http\Controllers\Timeline\TimelineController;
use App\Http\Controllers\Visit\ParticipantController;
use App\Http\Controllers\Visit\VisitController;
use App\Http\Controllers\Visitor\VisitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');

// Route::get('/offerings', function () {
//     return view('offerings');
// })->name('offerings');

Route::get('/reports', function () {
    return view('reports');
})->name('reports');

Route::get('/subscription', function () {
    return view('subscription');
})->name('subscription');

Route::get('/admin', function () {
    return view('admin');
})->name('admin')->middleware(['auth', 'role:super_admin,company_owner']);

Route::get('/settings', function () {
    return view('settings');
})->name('settings');

Route::resource('visitors', VisitorController::class)
    ->except(['show', 'destroy'])
    ->parameters(['visitors' => 'vin'])
    ->middleware('auth');

Route::get('visitors/{vin}/workspace', [VisitorController::class, 'workspace'])
    ->name('visitors.workspace')
    ->middleware('auth');

Route::post('visitors/{vin}/archive', [VisitorController::class, 'archive'])
    ->name('visitors.archive')
    ->middleware('auth');

Route::post('visitors/{vin}/restore', [VisitorController::class, 'restore'])
    ->name('visitors.restore')
    ->middleware('auth');

Route::get('visitors/{vin}/relationships', [RelationshipController::class, 'index'])
    ->name('visitors.relationships.index')
    ->middleware('auth');

Route::post('visitors/{vin}/relationships', [RelationshipController::class, 'store'])
    ->name('visitors.relationships.store')
    ->middleware('auth');

Route::post('visitors/{vin}/relationships/transfer', [RelationshipController::class, 'transfer'])
    ->name('visitors.relationships.transfer')
    ->middleware('auth');

Route::post('visitors/{vin}/relationships/approve', [RelationshipController::class, 'approve'])
    ->name('visitors.relationships.approve')
    ->middleware('auth');

Route::post('visitors/{vin}/relationships/reject', [RelationshipController::class, 'reject'])
    ->name('visitors.relationships.reject')
    ->middleware('auth');

Route::get('visitors/{vin}/visits', [VisitController::class, 'index'])
    ->name('visitors.visits.index');

Route::post('visitors/{vin}/visits', [VisitController::class, 'store'])
    ->name('visitors.visits.store');

Route::get('visitors/{vin}/visits/{visitId}', [VisitController::class, 'show'])
    ->name('visitors.visits.show');

Route::post('participants/{participantId}/promote', [ParticipantController::class, 'promote'])
    ->name('participants.promote');

Route::get('visitors/{vin}/communications', [CommunicationController::class, 'index'])
    ->name('visitors.communications.index');

Route::post('visitors/{vin}/communications', [CommunicationController::class, 'store'])
    ->name('visitors.communications.store');

Route::get('visitors/{vin}/communications/{communicationId}', [CommunicationController::class, 'show'])
    ->name('visitors.communications.show');

Route::get('knowledge-items', [KnowledgeItemController::class, 'index'])
    ->name('knowledge-items.index');

Route::post('knowledge-items', [KnowledgeItemController::class, 'store'])
    ->name('knowledge-items.store');

Route::get('knowledge-items/{itemId}', [KnowledgeItemController::class, 'show'])
    ->name('knowledge-items.show');

Route::post('knowledge-items/{itemId}/share', [KnowledgeItemController::class, 'share'])
    ->name('knowledge-items.share');

Route::delete('knowledge-items/{itemId}/share/{vin}', [KnowledgeItemController::class, 'revoke'])
    ->name('knowledge-items.revoke');

Route::get('visitors/{vin}/knowledge', [KnowledgeItemController::class, 'visitorKnowledge'])
    ->name('visitors.knowledge.index');

Route::get('visitors/{vin}/purchases', [PurchaseController::class, 'index'])
    ->name('visitors.purchases.index');

Route::post('visitors/{vin}/purchases', [PurchaseController::class, 'store'])
    ->name('visitors.purchases.store');

Route::get('visitors/{vin}/purchases/{purchaseId}', [PurchaseController::class, 'show'])
    ->name('visitors.purchases.show');

Route::get('visitors/{vin}/expenses', [ExpenseController::class, 'index'])
    ->name('visitors.expenses.index');

Route::post('visitors/{vin}/expenses', [ExpenseController::class, 'store'])
    ->name('visitors.expenses.store');

Route::get('visitors/{vin}/timeline', [TimelineController::class, 'index'])
    ->name('visitors.timeline.index');

Route::get('visitors/{vin}/timeline/{evn}', [TimelineController::class, 'show'])
    ->name('visitors.timeline.show');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,company_owner'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');

    Route::get('/system-tags', [SystemTagController::class, 'index'])->name('system-tags.index');
    Route::get('/system-tags/create', [SystemTagController::class, 'create'])->name('system-tags.create');
    Route::post('/system-tags', [SystemTagController::class, 'store'])->name('system-tags.store');
    Route::delete('/system-tags/{id}', [SystemTagController::class, 'destroy'])->name('system-tags.destroy');
});

// Offering Management Routes (MOD-009)
Route::get('/offerings', [OfferingController::class, 'index'])
    ->name('offerings.index');

Route::get('/offerings/create', [OfferingController::class, 'create'])
    ->name('offerings.create');

Route::post('/offerings', [OfferingController::class, 'store'])
    ->name('offerings.store');

Route::get('/offerings/{off}/edit', [OfferingController::class, 'edit'])
    ->name('offerings.edit');

Route::put('/offerings/{off}', [OfferingController::class, 'update'])
    ->name('offerings.update');

Route::delete('/offerings/{off}', [OfferingController::class, 'destroy'])
    ->name('offerings.destroy');
