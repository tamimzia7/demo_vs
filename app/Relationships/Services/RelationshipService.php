<?php

namespace App\Relationships\Services;

use App\Models\Relationship;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RelationshipService
{
    public function getRelationshipForVisitor(string $visitorVin, int $tenantId): ?Relationship
    {
        return Relationship::where('visitor_vin', $visitorVin)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function assignRelationship(string $visitorVin, int $marketerId, int $tenantId): Relationship
    {
        return DB::transaction(function () use ($visitorVin, $marketerId, $tenantId) {
            $existing = $this->getRelationshipForVisitor($visitorVin, $tenantId);

            if ($existing && $existing->status !== 'unassigned') {
                throw new \RuntimeException('Relationship already assigned to a marketer.');
            }

            if ($existing) {
                $existing->update([
                    'marketer_id' => $marketerId,
                    'status' => 'assigned',
                ]);

                $relationship = $existing->fresh();
            } else {
                $relationship = Relationship::create([
                    'tenant_id' => $tenantId,
                    'visitor_vin' => $visitorVin,
                    'marketer_id' => $marketerId,
                    'status' => 'assigned',
                ]);
            }

            $this->recordEvent($relationship, 'RelationshipAssigned', 'Relationship assigned');

            return $relationship->fresh();
        });
    }

    public function requestTransfer(string $visitorVin, int $targetMarketerId, int $tenantId): Relationship
    {
        return DB::transaction(function () use ($visitorVin, $tenantId) {
            $relationship = $this->getRelationshipForVisitor($visitorVin, $tenantId);

            if (! $relationship || $relationship->status !== 'assigned') {
                throw new \RuntimeException('No active relationship to transfer.');
            }

            $relationship->update([
                'status' => 'transfer_requested',
                'transferred_from_id' => $relationship->marketer_id,
            ]);

            return $relationship->fresh();
        });
    }

    public function approveTransfer(string $visitorVin, int $tenantId): Relationship
    {
        return DB::transaction(function () use ($visitorVin, $tenantId) {
            $relationship = $this->getRelationshipForVisitor($visitorVin, $tenantId);

            if (! $relationship || $relationship->status !== 'transfer_requested') {
                throw new \RuntimeException('No pending transfer request to approve.');
            }

            $relationship->update([
                'status' => 'transferred',
            ]);

            $this->recordEvent($relationship, 'RelationshipTransferred', 'Relationship transferred');

            return $relationship->fresh();
        });
    }

    public function rejectTransfer(string $visitorVin, int $tenantId): Relationship
    {
        return DB::transaction(function () use ($visitorVin, $tenantId) {
            $relationship = $this->getRelationshipForVisitor($visitorVin, $tenantId);

            if (! $relationship || $relationship->status !== 'transfer_requested') {
                throw new \RuntimeException('No pending transfer request to reject.');
            }

            $relationship->update([
                'status' => 'rejected',
                'transferred_from_id' => null,
            ]);

            return $relationship->fresh();
        });
    }

    public function getMarketers(int $tenantId)
    {
        return User::where('tenant_id', $tenantId)->get();
    }

    /**
     * Records a System-Generated Timeline Event for the relationship (BDR-016,
     * MOD-003 §H). MOD-002 consumes these events; MOD-003 is the producer.
     */
    protected function recordEvent(Relationship $relationship, string $source, string $summary): void
    {
        TimelineEvent::create([
            'evn' => $this->generateEvn(),
            'tenant_id' => $relationship->tenant_id,
            'visitor_vin' => $relationship->visitor_vin,
            'type' => 'system',
            'source' => $source,
            'summary' => $summary,
        ]);
    }

    protected function generateEvn(): string
    {
        $max = TimelineEvent::max('evn');

        $next = $max ? ((int) substr($max, -6)) + 1 : 1;

        return sprintf('EVN-%06d', $next);
    }
}
