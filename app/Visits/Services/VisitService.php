<?php

namespace App\Visits\Services;

use App\Models\Visit;
use App\Models\VisitParticipant;
use App\Timeline\Services\TimelineService;
use Illuminate\Support\Facades\DB;

class VisitService
{
    public function __construct(
        private TimelineService $timelineService
    ) {}

    public function getVisitsForVisitor(string $visitorVin, int $tenantId)
    {
        return Visit::where('visitor_vin', $visitorVin)
            ->where('tenant_id', $tenantId)
            ->with('participants')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getVisitById(int $visitId, int $tenantId): ?Visit
    {
        return Visit::where('id', $visitId)
            ->where('tenant_id', $tenantId)
            ->with('participants')
            ->first();
    }

    public function createVisit(array $data, string $visitorVin, int $tenantId): Visit
    {
        return DB::transaction(function () use ($data, $visitorVin, $tenantId) {
            $visit = Visit::create([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'visit_date' => $data['visit_date'],
                'context' => $data['context'] ?? null,
                'outcome' => $data['outcome'] ?? null,
            ]);

            if (! empty($data['participants']) && is_array($data['participants'])) {
                foreach ($data['participants'] as $participantName) {
                    $visit->participants()->create([
                        'tenant_id' => $tenantId,
                        'name' => $participantName,
                    ]);
                }
            }

            $this->timelineService->appendEvent([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'type' => 'user',
                'source' => 'Visit',
                'summary' => sprintf(
                    'Visit logged on %s (%s)',
                    $visit->visit_date->format('M d, Y'),
                    $visit->context ?? 'No context'
                ),
            ]);

            return $visit->fresh('participants');
        });
    }

    public function getParticipantsForVisit(int $visitId, int $tenantId)
    {
        return VisitParticipant::where('visit_id', $visitId)
            ->where('tenant_id', $tenantId)
            ->get();
    }

    public function getParticipantById(int $participantId, int $tenantId): ?VisitParticipant
    {
        return VisitParticipant::where('id', $participantId)
            ->where('tenant_id', $tenantId)
            ->first();
    }
}
