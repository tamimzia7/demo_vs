<?php

namespace App\Communication\Services;

use App\Communication\Enums\Channel;
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
            $channel = Channel::from($data['channel']);

            $communication = Communication::create([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'channel' => $channel,
                'content' => $data['content'] ?? null,
                'notice_id' => $data['notice_id'] ?? null,
                'sent_at' => now(),
            ]);

            $eventType = $channel->isSystemGenerated() ? 'system' : 'user';
            $eventSource = $this->getEventSource($channel);
            $summary = $this->buildEventSummary($communication, $channel);

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

    private function getEventSource(Channel $channel): string
    {
        return match ($channel) {
            Channel::SMS => 'SMS Sent',
            Channel::Email => 'Email Sent',
            Channel::Notice => 'Notice Sent',
            Channel::Call => 'Call',
            Channel::Meeting => 'Meeting',
        };
    }

    private function buildEventSummary(Communication $communication, Channel $channel): string
    {
        $channelName = $channel->label();

        if ($communication->content) {
            return sprintf('%s sent: %s', $channelName, Str::limit($communication->content, 100));
        }

        return sprintf('%s logged', $channelName);
    }
}
