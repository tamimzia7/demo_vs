<?php

namespace App\Visits\Services;

use App\Models\Visitor;
use App\Models\VisitParticipant;
use App\Timeline\Services\TimelineService;
use App\Visitors\Services\VisitorService;
use Illuminate\Support\Facades\DB;

class ParticipantPromotionService
{
    public function __construct(
        private VisitorService $visitorService,
        private TimelineService $timelineService
    ) {}

    public function promoteParticipant(VisitParticipant $participant, int $tenantId): Visitor
    {
        return DB::transaction(function () use ($participant, $tenantId) {
            $visitor = $this->visitorService->createVisitor(
                ['name' => $participant->name],
                $tenantId
            );

            $participant->update(['promoted_to_vin' => $visitor->vin]);

            $this->timelineService->appendEvent([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitor->vin,
                'type' => 'system',
                'source' => 'Visitor Created',
                'summary' => sprintf(
                    'Participant "%s" promoted to Visitor (%s)',
                    $participant->name,
                    $visitor->vin
                ),
            ]);

            return $visitor;
        });
    }
}
