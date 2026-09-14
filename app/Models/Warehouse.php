<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'type',
        'capacity_tons',
        'manager_id',
    ];

    protected function casts(): array
    {
        return [
            'capacity_tons' => 'integer',
        ];
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function complianceCertificates()
    {
        return $this->hasMany(ComplianceCertificate::class);
    }

    public function currentStockKg(): float
    {
        return (float) $this->inventoryItems()->sum('quantity_kg');
    }
}
