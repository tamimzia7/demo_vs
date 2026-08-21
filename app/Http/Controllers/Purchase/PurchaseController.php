<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\RecordPurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Purchase\Services\PurchaseService;

class PurchaseController extends Controller
{
    public function __construct(
        private PurchaseService $purchaseService
    ) {}

    public function index(string $vin)
    {
        $purchases = $this->purchaseService->getPurchasesForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        return PurchaseResource::collection($purchases);
    }

    public function store(RecordPurchaseRequest $request, string $vin)
    {
        $purchase = $this->purchaseService->recordPurchase(
            $request->validated(),
            $vin,
            auth()->user()->tenant_id
        );

        return new PurchaseResource($purchase);
    }

    public function show(string $vin, int $purchaseId)
    {
        $purchase = $this->purchaseService->getPurchaseById(
            $purchaseId,
            auth()->user()->tenant_id
        );

        if (! $purchase || $purchase->visitor_vin !== $vin) {
            abort(404);
        }

        return new PurchaseResource($purchase);
    }
}
