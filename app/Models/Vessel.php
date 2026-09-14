<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Vessel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registration_number',
        'home_port',
        'captain_name',
        'capacity_tons',
        'current_lat',
        'current_lng',
        'status',
        'estimated_arrival',
        'last_position_at',
    ];

    protected function casts(): array
    {
        return [
            'current_lat' => 'decimal:6',
            'current_lng' => 'decimal:6',
            'estimated_arrival' => 'datetime',
            'last_position_at' => 'datetime',
            'capacity_tons' => 'integer',
        ];
    }

    public function complianceCertificates()
    {
        return $this->hasMany(ComplianceCertificate::class);
    }

    public function scopeAtSea(Builder $query): Builder
    {
        return $query->where('status', 'at_sea');
    }

    public function scopeDocked(Builder $query): Builder
    {
        return $query->where('status', 'docked');
    }

    /**
     * Vessels whose latest compliance certificate has lapsed —
     * these should not be cleared to depart.
     */
    public function scopeWithExpiredCertificates(Builder $query): Builder
    {
        return $query->whereHas('complianceCertificates', function (Builder $q) {
            $q->where('expires_at', '<', now());
        });
    }

    public function isClearedToSail(): bool
    {
        return ! $this->complianceCertificates()
            ->where('expires_at', '<', now())
            ->exists();
    }
}
