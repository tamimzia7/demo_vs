<?php

namespace App\Knowledge\Services;

use App\Models\KnowledgeItem;
use App\Models\KnowledgeSharing;
use App\Timeline\Services\TimelineService;
use Illuminate\Support\Facades\DB;

class KnowledgeService
{
    public function __construct(
        private TimelineService $timelineService
    ) {}

    public function generateKnw(): string
    {
        $year = now()->format('Y');
        $lastItem = KnowledgeItem::where('knw', 'like', "KNW-{$year}-%")
            ->orderByDesc('knw')
            ->first();

        if ($lastItem) {
            $lastNumber = (int) substr($lastItem->knw, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('KNW-%s-%06d', $year, $nextNumber);
    }

    public function getItemsForTenant(int $tenantId)
    {
        return KnowledgeItem::where('tenant_id', $tenantId)
            ->with('activeSharings')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getItemById(int $itemId, int $tenantId): ?KnowledgeItem
    {
        return KnowledgeItem::where('id', $itemId)
            ->where('tenant_id', $tenantId)
            ->with('sharings')
            ->first();
    }

    public function getItemByKnw(string $knw, int $tenantId): ?KnowledgeItem
    {
        return KnowledgeItem::where('knw', $knw)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function createItem(array $data, int $tenantId): KnowledgeItem
    {
        return DB::transaction(function () use ($data, $tenantId) {
            $knw = $this->generateKnw();

            return KnowledgeItem::create([
                'tenant_id' => $tenantId,
                'knw' => $knw,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'link' => $data['link'],
                'version' => 1,
            ]);
        });
    }

    public function grantAccess(KnowledgeItem $item, string $visitorVin, int $tenantId): KnowledgeSharing
    {
        return DB::transaction(function () use ($item, $visitorVin, $tenantId) {
            $existing = KnowledgeSharing::where('knowledge_item_id', $item->id)
                ->where('visitor_vin', $visitorVin)
                ->where('tenant_id', $tenantId)
                ->first();

            if ($existing && $existing->isGranted()) {
                throw new \RuntimeException('Access already granted for this visitor.');
            }

            if ($existing) {
                $existing->update([
                    'status' => 'granted',
                    'revoked_at' => null,
                ]);

                $sharing = $existing->fresh();
            } else {
                $sharing = KnowledgeSharing::create([
                    'knowledge_item_id' => $item->id,
                    'tenant_id' => $tenantId,
                    'visitor_vin' => $visitorVin,
                    'status' => 'granted',
                ]);
            }

            $this->timelineService->appendEvent([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'type' => 'system',
                'source' => 'Knowledge Shared',
                'summary' => sprintf(
                    'Knowledge item "%s" (%s) shared',
                    $item->title,
                    $item->knw
                ),
            ]);

            return $sharing;
        });
    }

    public function revokeAccess(KnowledgeItem $item, string $visitorVin, int $tenantId): ?KnowledgeSharing
    {
        return DB::transaction(function () use ($item, $visitorVin, $tenantId) {
            $sharing = KnowledgeSharing::where('knowledge_item_id', $item->id)
                ->where('visitor_vin', $visitorVin)
                ->where('tenant_id', $tenantId)
                ->where('status', 'granted')
                ->first();

            if (! $sharing) {
                throw new \RuntimeException('No active sharing found for this visitor.');
            }

            $sharing->update([
                'status' => 'revoked',
                'revoked_at' => now(),
            ]);

            return $sharing->fresh();
        });
    }

    public function getItemsSharedWithVisitor(string $visitorVin, int $tenantId)
    {
        return KnowledgeItem::where('tenant_id', $tenantId)
            ->whereHas('sharings', function ($query) use ($visitorVin) {
                $query->where('visitor_vin', $visitorVin)
                    ->where('status', 'granted');
            })
            ->with(['sharings' => function ($query) use ($visitorVin) {
                $query->where('visitor_vin', $visitorVin);
            }])
            ->orderByDesc('created_at')
            ->get();
    }
}
