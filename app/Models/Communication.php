<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Communication extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'visitor_vin',
        'channel',
        'content',
        'notice_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isSms(): bool
    {
        return $this->channel === 'sms';
    }

    public function isEmail(): bool
    {
        return $this->channel === 'email';
    }

    public function isNotice(): bool
    {
        return $this->channel === 'notice';
    }

    public function isCall(): bool
    {
        return $this->channel === 'call';
    }

    public function isMeeting(): bool
    {
        return $this->channel === 'meeting';
    }

    public function isSystemGenerated(): bool
    {
        return in_array($this->channel, ['sms', 'email', 'notice']);
    }

    public function isUserGenerated(): bool
    {
        return in_array($this->channel, ['call', 'meeting']);
    }
}
