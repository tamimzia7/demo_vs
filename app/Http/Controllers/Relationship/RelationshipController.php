<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Relationship\AssignRelationshipRequest;
use App\Http\Requests\Relationship\TransferApproveRequest;
use App\Http\Requests\Relationship\TransferRequestRequest;
use App\Models\Visitor;
use App\Relationships\Services\RelationshipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RelationshipController extends Controller
{
    public function __construct(
        private RelationshipService $relationshipService
    ) {}

    public function index(string $vin)
    {
        $this->authorize('viewAny', \App\Models\Relationship::class);

        $relationship = $this->relationshipService->getRelationshipForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $relationship, 'meta' => []]);
    }

    public function store(AssignRelationshipRequest $request, string $vin)
    {
        $this->authorize('assign', \App\Models\Relationship::class);

        $visitor = Visitor::where('vin', $vin)->first();

        if (! $visitor) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $relationship = $this->relationshipService->assignRelationship(
            $vin,
            (int) $request->validated('marketer_id'),
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $relationship, 'meta' => []], Response::HTTP_CREATED);
    }

    public function transfer(TransferRequestRequest $request, string $vin)
    {
        $relationship = $this->relationshipService->getRelationshipForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        if (! $relationship) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $this->authorize('requestTransfer', $relationship);

        try {
            $relationship = $this->relationshipService->requestTransfer(
                $vin,
                (int) $request->validated('target_marketer_id'),
                auth()->user()->tenant_id
            );
        } catch (\RuntimeException $e) {
            return response()->json(['error' => ['message' => $e->getMessage()]], Response::HTTP_CONFLICT);
        }

        return response()->json(['data' => $relationship, 'meta' => []]);
    }

    public function approve(TransferApproveRequest $request, string $vin)
    {
        $relationship = $this->relationshipService->getRelationshipForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        if (! $relationship) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $this->authorize('approve', $relationship);

        try {
            $relationship = $this->relationshipService->approveTransfer(
                $vin,
                auth()->user()->tenant_id
            );
        } catch (\RuntimeException $e) {
            return response()->json(['error' => ['message' => $e->getMessage()]], Response::HTTP_CONFLICT);
        }

        return response()->json(['data' => $relationship, 'meta' => []]);
    }

    public function reject(TransferApproveRequest $request, string $vin)
    {
        $relationship = $this->relationshipService->getRelationshipForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        if (! $relationship) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $this->authorize('reject', $relationship);

        try {
            $relationship = $this->relationshipService->rejectTransfer(
                $vin,
                auth()->user()->tenant_id
            );
        } catch (\RuntimeException $e) {
            return response()->json(['error' => ['message' => $e->getMessage()]], Response::HTTP_CONFLICT);
        }

        return response()->json(['data' => $relationship, 'meta' => []]);
    }
}
