<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Relationships\Services\RelationshipService;
use Illuminate\Http\Request;

class RelationshipController extends Controller
{
    public function __construct(
        private RelationshipService $relationshipService
    ) {}

    public function index(string $vin)
    {
        $relationship = $this->relationshipService->getRelationshipForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $relationship]);
    }

    public function store(Request $request, string $vin)
    {
        $validated = $request->validate([
            'marketer_id' => 'required|exists:users,id',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $marketer = User::where('id', $validated['marketer_id'])
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $marketer) {
            abort(404, 'Marketer not found.');
        }

        $relationship = $this->relationshipService->assignRelationship(
            $vin,
            $validated['marketer_id'],
            $tenantId
        );

        return response()->json(['data' => $relationship], 201);
    }

    public function transfer(Request $request, string $vin)
    {
        $validated = $request->validate([
            'target_marketer_id' => 'required|exists:users,id',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $marketer = User::where('id', $validated['target_marketer_id'])
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $marketer) {
            abort(404, 'Target marketer not found.');
        }

        $relationship = $this->relationshipService->requestTransfer(
            $vin,
            $validated['target_marketer_id'],
            $tenantId
        );

        return response()->json(['data' => $relationship]);
    }

    public function approve(string $vin)
    {
        $relationship = $this->relationshipService->approveTransfer(
            $vin,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $relationship]);
    }

    public function reject(string $vin)
    {
        $relationship = $this->relationshipService->rejectTransfer(
            $vin,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $relationship]);
    }
}
