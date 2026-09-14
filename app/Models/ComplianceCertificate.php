<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ComplianceCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'vessel_id',
        'warehouse_id',
        'certificate_type',
        'certificate_number',
        'issuing_authority',
        'issued_at',
        'expires_at',
        'document_url',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function vessel()
    {
        return $this->belongsTo(Vessel::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * Human status: expired | expiring_soon | valid
     */
    public function getStatusAttribute(): string
    {
        if ($this->expires_at->isPast()) {
            return 'expired';
        }

        if ($this->expires_at->lte(now()->addDays(30))) {
            return 'expiring_soon';
        }

        return 'valid';
    }
}
