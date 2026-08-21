<?php

namespace App\Http\Controllers\Communication;

use App\Communication\Services\CommunicationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        return response()->json(['data' => $communications]);
    }

    public function store(Request $request, string $vin)
    {
        $validated = $request->validate([
            'channel' => 'required|in:sms,email,notice,call,meeting',
            'content' => 'nullable|string|max:5000',
            'notice_id' => 'nullable|integer',
        ]);

        $communication = $this->communicationService->createCommunication(
            $validated,
            $vin,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $communication], 201);
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

        return response()->json(['data' => $communication]);
    }
}
