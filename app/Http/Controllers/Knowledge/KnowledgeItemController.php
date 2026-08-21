<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Knowledge\Services\KnowledgeService;
use App\Models\Visitor;
use Illuminate\Http\Request;

class KnowledgeItemController extends Controller
{
    public function __construct(
        private KnowledgeService $knowledgeService
    ) {}

    public function index()
    {
        $items = $this->knowledgeService->getItemsForTenant(
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'link' => 'required|url|max:2048',
        ]);

        $item = $this->knowledgeService->createItem(
            $validated,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $item], 201);
    }

    public function show(int $itemId)
    {
        $item = $this->knowledgeService->getItemById(
            $itemId,
            auth()->user()->tenant_id
        );

        if (! $item) {
            abort(404);
        }

        return response()->json(['data' => $item]);
    }

    public function share(Request $request, int $itemId)
    {
        $validated = $request->validate([
            'vin' => 'required|string|max:20',
        ]);

        $item = $this->knowledgeService->getItemById(
            $itemId,
            auth()->user()->tenant_id
        );

        if (! $item) {
            abort(404);
        }

        $visitor = Visitor::where('vin', $validated['vin'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (! $visitor) {
            abort(404, 'Visitor not found.');
        }

        $sharing = $this->knowledgeService->grantAccess(
            $item,
            $validated['vin'],
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $sharing], 201);
    }

    public function revoke(int $itemId, string $vin)
    {
        $item = $this->knowledgeService->getItemById(
            $itemId,
            auth()->user()->tenant_id
        );

        if (! $item) {
            abort(404);
        }

        $sharing = $this->knowledgeService->revokeAccess(
            $item,
            $vin,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $sharing]);
    }

    public function visitorKnowledge(string $vin)
    {
        $items = $this->knowledgeService->getItemsSharedWithVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $items]);
    }
}
