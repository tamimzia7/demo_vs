<?php

namespace App\Offerings\Services;

use App\Models\Offering;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

class OfferingService
{
    public function generateOff(): string
    {
        $year = now()->format('Y');
        $lastOffering = Offering::where('off', 'like', "OFF-{$year}-%")
            ->orderByDesc('off')
            ->first();

        if ($lastOffering) {
            $lastNumber = (int) substr($lastOffering->off, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('OFF-%s-%06d', $year, $nextNumber);
    }

    public function getOfferings(int $tenantId, ?string $search = null, ?bool $active = null)
    {
        $query = Offering::where('tenant_id', $tenantId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('off', 'like', "%{$search}%");
            });
        }

        if ($active !== null) {
            $query->where('active', $active);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function getOfferingByOff(string $off, int $tenantId): ?Offering
    {
        return Offering::where('off', $off)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function createOffering(array $data, int $tenantId): Offering
    {
        return DB::transaction(function () use ($data, $tenantId) {
            $off = $this->generateOff();

            return Offering::create([
                'tenant_id' => $tenantId,
                'off' => $off,
                'name' => $data['name'],
                'metadata' => $data['metadata'] ?? null,
                'active' => $data['active'] ?? true,
            ]);
        });
    }

    public function updateOffering(Offering $offering, array $data): Offering
    {
        $offering->update($data);

        return $offering->fresh();
    }

    public function getVisitorsForOffering(string $off, int $tenantId)
    {
        $offering = $this->getOfferingByOff($off, $tenantId);

        if (! $offering) {
            return collect();
        }

        return Visitor::whereHas('purchases', function ($query) use ($offering) {
            $query->where('offering_id', $offering->id);
        })
            ->where('tenant_id', $tenantId)
            ->distinct()
            ->get();
    }

    public function getOfferingsForVisitor(string $vin, int $tenantId)
    {
        return Offering::whereHas('purchases', function ($query) use ($vin) {
            $query->where('visitor_vin', $vin);
        })
            ->where('tenant_id', $tenantId)
            ->distinct()
            ->get();
    }
}
