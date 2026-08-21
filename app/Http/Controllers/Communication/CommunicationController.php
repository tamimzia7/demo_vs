<?php

namespace App\Http\Controllers\Communication;

use App\Communication\Services\CommunicationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\SendCommunicationRequest;
use App\Http\Resources\CommunicationResource;

class CommunicationController extends Controller
{
    public function __construct(
        private CommunicationService $communicationService
    ) {}

    public function index(string $vin)
    {
        $communications = $this->communicationService->getCommunicationsForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        return CommunicationResource::collection($communications);
    }

    public function store(SendCommunicationRequest $request, string $vin)
    {
        $communication = $this->communicationService->createCommunication(
            $request->validated(),
            $vin,
            auth()->user()->tenant_id
        );

        return new CommunicationResource($communication);
    }

    public function show(string $vin, int $communicationId)
    {
        $communication = $this->communicationService->getCommunicationById(
            $communicationId,
            auth()->user()->tenant_id
        );

        if (! $communication || $communication->visitor_vin !== $vin) {
            abort(404);
        }

        return new CommunicationResource($communication);
    }
}
