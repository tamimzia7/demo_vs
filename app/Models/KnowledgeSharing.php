<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeSharing extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_item_id',
        'tenant_id',
        'visitor_vin',
        'status',
        'revoked_at',
    ];

    protected $casts = [
        'revoked_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function knowledgeItem(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class);
    }

    public function isGranted(): bool
    {
        return $this->status === 'granted';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }
}
