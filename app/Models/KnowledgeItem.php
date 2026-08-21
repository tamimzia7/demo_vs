<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'knw',
        'title',
        'description',
        'link',
        'version',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sharings(): HasMany
    {
        return $this->hasMany(KnowledgeSharing::class);
    }

    public function activeSharings(): HasMany
    {
        return $this->hasMany(KnowledgeSharing::class)->where('status', 'granted');
    }
}
