<?php

namespace App\Http\Controllers\Timeline;

use App\Http\Controllers\Controller;
use App\Timeline\Services\TimelineService;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function __construct(
        private TimelineService $timelineService
    ) {}

    public function index(Request $request, string $vin)
    {
        $tenantId = auth()->user()->tenant_id;
        $type = $request->input('type');

        $events = $this->timelineService->getEventsForVisitor(
            $vin,
            $tenantId,
            $type
        );

        return response()->json(['data' => $events]);
    }

    public function show(string $vin, string $evn)
    {
        $event = $this->timelineService->getEventByEvn(
            $evn,
            $vin,
            auth()->user()->tenant_id
        );

        if (! $event) {
            abort(404);
        }

        return response()->json(['data' => $event]);
    }
}
