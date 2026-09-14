<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'name',
        'sku',
        'category',
        'species',
        'quantity_kg',
        'reorder_threshold_kg',
        'unit_price',
        'caught_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_kg' => 'decimal:2',
            'reorder_threshold_kg' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'caught_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity_kg', '<=', 'reorder_threshold_kg');
    }

    public function scopeExpiringWithin(Builder $query, int $days = 7): Builder
    {
        return $query->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    public function isLowStock(): bool
    {
        return $this->quantity_kg <= $this->reorder_threshold_kg;
    }
}
