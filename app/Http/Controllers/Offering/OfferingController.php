<?php

namespace App\Http\Controllers\Offering;

use App\Http\Controllers\Controller;
use App\Http\Requests\Offering\StoreOfferingRequest;
use App\Http\Requests\Offering\UpdateOfferingRequest;
use App\Http\Resources\Offering\OfferingResource;
use App\Offerings\Services\OfferingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class OfferingController extends Controller
{
    public function __construct(
        private OfferingService $offeringService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user() ?? Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $tenantId = $user->tenant_id;
        $search = $request->input('search');
        $active = $request->input('active');

        $offerings = $this->offeringService->getOfferings($tenantId, $search,
            $active === null ? null : filter_var($active, FILTER_VALIDATE_BOOLEAN));

        return view('offering.index', compact('offerings'));
    }

    public function create()
    {
        return view('offering.create');
    }

    public function store(StoreOfferingRequest $request)
    {
        $user = $request->user() ?? Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $tenantId = $user->tenant_id;
        $data = $request->validated();

        if (isset($data['metadata']) && is_string($data['metadata'])) {
            $data['metadata'] = json_decode($data['metadata'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data['metadata'] = null;
            }
        }

        $offering = $this->offeringService->createOffering($data, $tenantId);

        return redirect()->route('offerings.index')
            ->with('success', 'Offering created successfully.');
    }

    public function edit(string $off, Request $request)
    {
        $user = $request->user() ?? Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $tenantId = $user->tenant_id;
        $offering = $this->offeringService->getOfferingByOff($off, $tenantId);

        if (! $offering) {
            abort(404);
        }

        return view('offering.edit', compact('offering'));
    }

    public function update(UpdateOfferingRequest $request, string $off)
    {
        $user = $request->user() ?? Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $tenantId = $user->tenant_id;
        $offering = $this->offeringService->getOfferingByOff($off, $tenantId);

        if (! $offering) {
            abort(404);
        }

        $data = $request->validated();

        if (isset($data['metadata']) && is_string($data['metadata'])) {
            $data['metadata'] = json_decode($data['metadata'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data['metadata'] = null;
            }
        }

        $offering = $this->offeringService->updateOffering($offering, $data);

        return redirect()->route('offerings.index')
            ->with('success', 'Offering updated successfully.');
    }

    public function destroy(string $off, Request $request)
    {
        $user = $request->user() ?? Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $tenantId = $user->tenant_id;
        $offering = $this->offeringService->getOfferingByOff($off, $tenantId);

        if (! $offering) {
            abort(404);
        }

        $offering->delete();

        return redirect()->route('offerings.index')
            ->with('success', 'Offering deleted successfully.');
    }

    public function apiIndex(Request $request): AnonymousResourceCollection
    {
        $user = $request->user() ?? Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $tenantId = $user->tenant_id;
        $search = $request->input('search');
        $active = $request->input('active');

        $offerings = $this->offeringService->getOfferings($tenantId, $search,
            $active === null ? null : filter_var($active, FILTER_VALIDATE_BOOLEAN));

        return OfferingResource::collection($offerings);
    }

    public function apiStore(StoreOfferingRequest $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $tenantId = $user->tenant_id;
        $data = $request->validated();

        if (isset($data['metadata']) && is_string($data['metadata'])) {
            $data['metadata'] = json_decode($data['metadata'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data['metadata'] = null;
            }
        }

        $offering = $this->offeringService->createOffering($data, $tenantId);

        return (new OfferingResource($offering))
            ->response()
            ->setStatusCode(201);
    }

    public function apiUpdate(UpdateOfferingRequest $request, string $off): OfferingResource|JsonResponse
    {
        $user = $request->user() ?? Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $tenantId = $user->tenant_id;
        $offering = $this->offeringService->getOfferingByOff($off, $tenantId);

        if (! $offering) {
            abort(404);
        }

        $data = $request->validated();

        if (isset($data['metadata']) && is_string($data['metadata'])) {
            $data['metadata'] = json_decode($data['metadata'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data['metadata'] = null;
            }
        }

        $offering = $this->offeringService->updateOffering($offering, $data);

        return new OfferingResource($offering);
    }
}
