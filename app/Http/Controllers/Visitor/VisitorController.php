<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Relationships\Services\RelationshipService;
use App\Timeline\Services\TimelineService;
use App\Visitors\Services\VisitorService;
use App\Visits\Services\VisitService;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function __construct(
        private VisitorService $visitorService,
        private RelationshipService $relationshipService,
        private TimelineService $timelineService,
        private VisitService $visitService
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $visitors = $this->visitorService->getVisitors(
            auth()->user()->tenant_id,
            $search
        );

        return view('visitors.index', compact('visitors', 'search'));
    }

    public function create()
    {
        return view('visitors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'nullable|string|max:255',
            'contact' => 'nullable|array',
            'referrer_vin' => 'nullable|string|max:20',
        ]);

        $visitor = $this->visitorService->createVisitor(
            $validated,
            auth()->user()->tenant_id
        );

        return redirect()->route('visitors.workspace', $visitor->vin)
            ->with('success', 'Visitor created successfully.');
    }

    public function workspace(string $vin)
    {
        $visitor = $this->visitorService->getVisitorByVin(
            $vin,
            auth()->user()->tenant_id
        );

        if (! $visitor) {
            abort(404);
        }

        $relationship = $this->relationshipService->getRelationshipForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        $marketers = User::where('tenant_id', auth()->user()->tenant_id)->get();

        $timelineEvents = $this->timelineService->getEventsForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        $visits = $this->visitService->getVisitsForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        return view('visitors.workspace', compact('visitor', 'relationship', 'marketers', 'timelineEvents', 'visits'));
    }

    public function edit(string $vin)
    {
        $visitor = $this->visitorService->getVisitorByVin(
            $vin,
            auth()->user()->tenant_id
        );

        if (! $visitor) {
            abort(404);
        }

        return view('visitors.edit', compact('visitor'));
    }

    public function update(Request $request, string $vin)
    {
        $visitor = $this->visitorService->getVisitorByVin(
            $vin,
            auth()->user()->tenant_id
        );

        if (! $visitor) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'nullable|string|max:255',
            'contact' => 'nullable|array',
        ]);

        $this->visitorService->updateVisitor($visitor, $validated);

        return redirect()->route('visitors.workspace', $visitor->vin)
            ->with('success', 'Visitor updated successfully.');
    }

    public function archive(string $vin)
    {
        $visitor = $this->visitorService->getVisitorByVin(
            $vin,
            auth()->user()->tenant_id
        );

        if (! $visitor) {
            abort(404);
        }

        $this->visitorService->archiveVisitor($visitor);

        return redirect()->route('visitors.index')
            ->with('success', 'Visitor archived successfully.');
    }

    public function restore(string $vin)
    {
        $visitor = $this->visitorService->getVisitorByVin(
            $vin,
            auth()->user()->tenant_id
        );

        if (! $visitor) {
            abort(404);
        }

        $this->visitorService->restoreVisitor($visitor);

        return redirect()->route('visitors.workspace', $visitor->vin)
            ->with('success', 'Visitor restored successfully.');
    }
}
