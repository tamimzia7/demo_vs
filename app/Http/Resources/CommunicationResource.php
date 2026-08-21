<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'channel' => $this->channel->value,
            'channel_label' => $this->channel->label(),
            'content' => $this->content,
            'notice_id' => $this->notice_id,
            'sent_at' => $this->sent_at->toIso8601String(),
            'type' => $this->channel->isSystemGenerated() ? 'system' : 'user',
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
