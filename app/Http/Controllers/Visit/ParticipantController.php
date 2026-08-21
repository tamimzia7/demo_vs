<?php

namespace App\Http\Controllers\Visit;

use App\Http\Controllers\Controller;
use App\Visits\Services\ParticipantPromotionService;
use App\Visits\Services\VisitService;

class ParticipantController extends Controller
{
    public function __construct(
        private ParticipantPromotionService $promotionService,
        private VisitService $visitService
    ) {}

    public function promote(int $participantId)
    {
        $participant = $this->visitService->getParticipantById(
            $participantId,
            auth()->user()->tenant_id
        );

        if (! $participant) {
            abort(404);
        }

        if ($participant->promoted_to_vin) {
            abort(422, 'Participant has already been promoted to a Visitor.');
        }

        $visitor = $this->promotionService->promoteParticipant(
            $participant,
            auth()->user()->tenant_id
        );

        return response()->json(['data' => $visitor], 201);
    }
}
