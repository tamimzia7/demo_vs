<?php

namespace App\Http\Controllers\Visitor;

use App\Communication\Services\CommunicationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Visitor\StoreVisitorRequest;
use App\Http\Requests\Visitor\UpdateVisitorRequest;
use App\Investment\Services\InvestmentService;
use App\Knowledge\Services\KnowledgeService;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\Visitor;
use App\Purchase\Services\PurchaseService;
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
        private VisitService $visitService,
        private CommunicationService $communicationService,
        private KnowledgeService $knowledgeService,
        private PurchaseService $purchaseService,
        private InvestmentService $investmentService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Visitor::class);

        $search = $request->input('search');
        $visitors = $this->visitorService->getVisitors(
            auth()->user()->tenant_id,
            $search
        );

        return view('visitors.index', compact('visitors', 'search'));
    }

    public function create()
    {
        $this->authorize('create', Visitor::class);

        return view('visitors.create');
    }

    public function store(StoreVisitorRequest $request)
    {
        $this->authorize('create', Visitor::class);

        $visitor = $this->visitorService->createVisitor(
            $request->validated(),
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

        $this->authorize('view', $visitor);

        $relationship = $this->relationshipService->getRelationshipForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        $marketers = User::where('tenant_id', auth()->user()->tenant_id)->get();

        $timelineEvents = TimelineEvent::where('visitor_vin', $vin)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('created_at')
            ->get();

        $visits = $this->visitService->getVisitsForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        $communications = $this->communicationService->getCommunicationsForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        $knowledgeItems = $this->knowledgeService->getItemsSharedWithVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        $purchases = $this->purchaseService->getPurchasesForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        $expenses = $this->investmentService->getExpensesForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        return view('visitors.workspace', compact('visitor', 'relationship', 'marketers', 'timelineEvents', 'visits', 'communications', 'knowledgeItems', 'purchases', 'expenses'));
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

        $this->authorize('update', $visitor);

        return view('visitors.edit', compact('visitor'));
    }

    public function update(UpdateVisitorRequest $request, string $vin)
    {
        $visitor = $this->visitorService->getVisitorByVin(
            $vin,
            auth()->user()->tenant_id
        );

        if (! $visitor) {
            abort(404);
        }

        $this->authorize('update', $visitor);

        $this->visitorService->updateVisitor($visitor, $request->validated());

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

        $this->authorize('archive', $visitor);

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

        $this->authorize('restore', $visitor);

        $this->visitorService->restoreVisitor($visitor);

        return redirect()->route('visitors.workspace', $visitor->vin)
            ->with('success', 'Visitor restored successfully.');
    }
}
