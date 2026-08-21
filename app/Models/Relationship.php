<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Relationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'visitor_vin',
        'marketer_id',
        'status',
        'transferred_from_id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function marketer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketer_id');
    }

    public function transferredFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_from_id');
    }

    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    public function isTransferRequested(): bool
    {
        return $this->status === 'transfer_requested';
    }

    public function isTransferred(): bool
    {
        return $this->status === 'transferred';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
