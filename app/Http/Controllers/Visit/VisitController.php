<?php

namespace App\Http\Controllers\Visit;

use App\Http\Controllers\Controller;
use App\Visits\Services\VisitService;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function __construct(
        private VisitService $visitService
    ) {}

    public function index(string $vin)
    {
        $visits = $this->visitService->getVisitsForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $visits]);
    }

    public function store(Request $request, string $vin)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'context' => 'nullable|string|max:255',
            'outcome' => 'nullable|string|max:255',
            'participants' => 'nullable|array',
            'participants.*' => 'string|max:255',
        ]);

        $visit = $this->visitService->createVisit(
            $validated,
            $vin,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $visit], 201);
    }

    public function show(string $vin, int $visitId)
    {
        $visit = $this->visitService->getVisitById(
            $visitId,
            auth()->user()->tenant_id
        );

        if (! $visit || $visit->visitor_vin !== $vin) {
            abort(404);
        }

        return response()->json(['data' => $visit]);
    }
}
