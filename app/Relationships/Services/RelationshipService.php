<?php

namespace App\Relationships\Services;

use App\Models\Relationship;
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

                return $existing->fresh();
            }

            return Relationship::create([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'marketer_id' => $marketerId,
                'status' => 'assigned',
            ]);
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
}
