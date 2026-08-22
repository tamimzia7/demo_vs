<?php

namespace App\Purchase\Services;

use App\Models\Purchase;
use App\Models\Visitor;
use App\Timeline\Services\TimelineService;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private TimelineService $timelineService
    ) {}

    public function getPurchasesForVisitor(string $visitorVin, int $tenantId)
    {
        return Purchase::where('visitor_vin', $visitorVin)
            ->where('tenant_id', $tenantId)
            ->orderByDesc('purchased_at')
            ->get();
    }

    public function getPurchaseById(int $purchaseId, int $tenantId): ?Purchase
    {
        return Purchase::where('id', $purchaseId)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function recordPurchase(array $data, string $visitorVin, int $tenantId): Purchase
    {
        return DB::transaction(function () use ($data, $visitorVin, $tenantId) {
            $visitor = Visitor::where('vin', $visitorVin)
                ->where('tenant_id', $tenantId)
                ->first();

            if (! $visitor) {
                abort(404);
            }

            $purchase = Purchase::create([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'offering_id' => $data['offering_id'] ?? null,
                'amount' => $data['amount'] ?? null,
                'purchased_at' => $data['purchased_at'] ?? now(),
            ]);

            $this->timelineService->appendEvent([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'type' => 'system',
                'source' => 'Purchase',
                'summary' => $this->buildEventSummary($purchase),
            ]);

            $this->advanceVisitorLifecycle($visitorVin, $tenantId);

            return $purchase;
        });
    }

    private function buildEventSummary(Purchase $purchase): string
    {
        $summary = 'Purchase recorded';

        if ($purchase->offering_id) {
            $summary .= ' for offering #'.$purchase->offering_id;
        }

        if ($purchase->amount) {
            $summary .= sprintf(' ($%s)', number_format($purchase->amount, 2));
        }

        return $summary;
    }

    private function advanceVisitorLifecycle(string $visitorVin, int $tenantId): void
    {
        $visitor = Visitor::where('vin', $visitorVin)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $visitor) {
            return;
        }

        $purchaseCount = Purchase::where('visitor_vin', $visitorVin)
            ->where('tenant_id', $tenantId)
            ->count();

        $newState = match (true) {
            $purchaseCount === 1 => 'Purchased',
            $purchaseCount > 1 => 'Repeat Customer',
            default => null,
        };

        if ($newState && $visitor->lifecycle_state !== $newState) {
            $oldState = $visitor->lifecycle_state;
            $visitor->update(['lifecycle_state' => $newState]);

            $this->timelineService->appendEvent([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'type' => 'system',
                'source' => 'Lifecycle Changed',
                'summary' => sprintf('Lifecycle changed from %s to %s', $oldState, $newState),
            ]);
        }
    }
}
