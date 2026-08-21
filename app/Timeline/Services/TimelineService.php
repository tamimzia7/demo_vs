<?php

namespace App\Timeline\Services;

use App\Models\TimelineEvent;
use Illuminate\Support\Facades\DB;

class TimelineService
{
    public function getEventsForVisitor(string $visitorVin, int $tenantId, ?string $type = null)
    {
        $query = TimelineEvent::where('visitor_vin', $visitorVin)
            ->where('tenant_id', $tenantId)
            ->whereNull('archived_at');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function getEventByEvn(string $evn, string $visitorVin, int $tenantId): ?TimelineEvent
    {
        return TimelineEvent::where('evn', $evn)
            ->where('visitor_vin', $visitorVin)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function generateEvn(): string
    {
        $year = now()->format('Y');
        $lastEvent = TimelineEvent::where('evn', 'like', "EVN-{$year}-%")
            ->orderByDesc('evn')
            ->first();

        if ($lastEvent) {
            $lastNumber = (int) substr($lastEvent->evn, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('EVN-%s-%06d', $year, $nextNumber);
    }

    public function appendEvent(array $data): TimelineEvent
    {
        return DB::transaction(function () use ($data) {
            $evn = $this->generateEvn();

            return TimelineEvent::create([
                'evn' => $evn,
                'tenant_id' => $data['tenant_id'],
                'visitor_vin' => $data['visitor_vin'],
                'type' => $data['type'],
                'source' => $data['source'],
                'summary' => $data['summary'],
            ]);
        });
    }
}
