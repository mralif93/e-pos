<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OfflineSaleDraft extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

        protected $fillable = [
            'id',
            'uuid',
            'user_id',        'outlet_id',
        'customer_id',
        'cart_data',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'discount_reason',
        'payments',
        'points_earned',
        'points_redeemed',
        'discount_from_points',
        'local_created_at',
        'synced',
        'synced_sale_id',
        'synced_at',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'payments' => 'array',
        'local_created_at' => 'datetime',
        'synced' => 'boolean',
        'synced_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_from_points' => 'decimal:2',
        'points_earned' => 'integer',
        'points_redeemed' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->local_created_at)) {
                $model->local_created_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(OfflineSaleItem::class, 'draft_id');
    }

    public function syncedSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'synced_sale_id');
    }

    public function markAsSynced(int $saleId): void
    {
        $this->update([
            'synced' => true,
            'synced_sale_id' => $saleId,
            'synced_at' => now(),
        ]);
    }

    public static function getUnsyncedDrafts()
    {
        return static::where('synced', false)->orderBy('local_created_at', 'asc');
    }
}
