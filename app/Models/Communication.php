<?php

namespace App\Models;

use App\Communication\Enums\Channel;
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
        'channel' => Channel::class,
        'sent_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }

    public function isSms(): bool
    {
        return $this->channel === Channel::SMS;
    }

    public function isEmail(): bool
    {
        return $this->channel === Channel::Email;
    }

    public function isNotice(): bool
    {
        return $this->channel === Channel::Notice;
    }

    public function isCall(): bool
    {
        return $this->channel === Channel::Call;
    }

    public function isMeeting(): bool
    {
        return $this->channel === Channel::Meeting;
    }

    public function isSystemGenerated(): bool
    {
        return $this->channel->isSystemGenerated();
    }

    public function isUserGenerated(): bool
    {
        return $this->channel->isUserGenerated();
    }
}
