<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'vin',
        'name',
        'channel',
        'contact',
        'referrer_vin',
        'lifecycle_state',
        'archived_at',
        'event_count',
    ];

    protected $casts = [
        'contact' => 'array',
        'archived_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isArchived(): bool
    {
        return $this->lifecycle_state === 'Archived';
    }

    public function archive(): void
    {
        $this->update([
            'lifecycle_state' => 'Archived',
            'archived_at' => now(),
        ]);
    }

    public function restore(): void
    {
        $this->update([
            'lifecycle_state' => 'Interested',
            'archived_at' => null,
        ]);
    }
}
