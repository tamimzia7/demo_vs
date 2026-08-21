<?php

namespace App\Communication\Services;

use App\Models\Communication;
use App\Timeline\Services\TimelineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommunicationService
{
    public function __construct(
        private TimelineService $timelineService
    ) {}

    public function getCommunicationsForVisitor(string $visitorVin, int $tenantId)
    {
        return Communication::where('visitor_vin', $visitorVin)
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getCommunicationById(int $communicationId, int $tenantId): ?Communication
    {
        return Communication::where('id', $communicationId)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function createCommunication(array $data, string $visitorVin, int $tenantId): Communication
    {
        return DB::transaction(function () use ($data, $visitorVin, $tenantId) {
            $communication = Communication::create([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'channel' => $data['channel'],
                'content' => $data['content'] ?? null,
                'notice_id' => $data['notice_id'] ?? null,
                'sent_at' => now(),
            ]);

            $eventType = $communication->isSystemGenerated() ? 'system' : 'user';
            $eventSource = $this->getEventSource($communication->channel);
            $summary = $this->buildEventSummary($communication);

            $this->timelineService->appendEvent([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'type' => $eventType,
                'source' => $eventSource,
                'summary' => $summary,
            ]);

            return $communication;
        });
    }

    private function getEventSource(string $channel): string
    {
        return match ($channel) {
            'sms' => 'SMS Sent',
            'email' => 'Email Sent',
            'notice' => 'Notice Sent',
            'call' => 'Call',
            'meeting' => 'Meeting',
            default => 'Communication',
        };
    }

    private function buildEventSummary(Communication $communication): string
    {
        $channelName = ucfirst($communication->channel);

        if ($communication->content) {
            return sprintf('%s sent: %s', $channelName, Str::limit($communication->content, 100));
        }

        return sprintf('%s logged', $channelName);
    }
}
