<?php

namespace App\Visitors\Services;

use App\Models\TimelineEvent;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

class VisitorService
{
    public function generateVin(): string
    {
        $year = now()->format('Y');
        $lastVisitor = Visitor::where('vin', 'like', "VC-{$year}-%")
            ->orderByDesc('vin')
            ->first();

        if ($lastVisitor) {
            $lastNumber = (int) substr($lastVisitor->vin, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('VC-%s-%06d', $year, $nextNumber);
    }

    public function getVisitors(int $tenantId, ?string $search = null)
    {
        $query = Visitor::where('tenant_id', $tenantId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function getVisitorByVin(string $vin, int $tenantId): ?Visitor
    {
        return Visitor::where('vin', $vin)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function createVisitor(array $data, int $tenantId): Visitor
    {
        return DB::transaction(function () use ($data, $tenantId) {
            $vin = $this->generateVin();

            $visitor = Visitor::create([
                'tenant_id' => $tenantId,
                'vin' => $vin,
                'name' => $data['name'],
                'channel' => $data['channel'] ?? null,
                'contact' => $data['contact'] ?? null,
                'referrer_vin' => $data['referrer_vin'] ?? null,
                'lifecycle_state' => 'Interested',
            ]);

            $this->recordVisitorCreatedEvent($visitor, $tenantId);

            return $visitor;
        });
    }

    /**
     * Records the VisitorCreated System Timeline Event (BDR-016 / MOD-001 §H).
     */
    protected function recordVisitorCreatedEvent(Visitor $visitor, int $tenantId): void
    {
        TimelineEvent::create([
            'evn' => $this->generateEvn(),
            'tenant_id' => $tenantId,
            'visitor_vin' => $visitor->vin,
            'type' => 'system',
            'source' => 'VisitorCreated',
            'summary' => 'Visitor created',
        ]);
    }

    protected function generateEvn(): string
    {
        $max = TimelineEvent::max('evn');

        $next = $max ? ((int) substr($max, -6)) + 1 : 1;

        return sprintf('EVN-%06d', $next);
    }

    public function updateVisitor(Visitor $visitor, array $data): Visitor
    {
        $visitor->update($data);

        return $visitor->fresh();
    }

    public function archiveVisitor(Visitor $visitor): Visitor
    {
        $visitor->archive();

        return $visitor->fresh();
    }

    public function restoreVisitor(Visitor $visitor): Visitor
    {
        $visitor->restore();

        return $visitor->fresh();
    }
}
