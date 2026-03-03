<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasUuids, HasFactory;

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected $fillable = ['uuid', 'name', 'phone', 'email', 'created_by', 'loyalty_points', 'total_points_earned', 'loyalty_tier', 'points_expiry_date'];

    protected $casts = [
        'points_expiry_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyPointTransaction::class);
    }

    public function addPoints(int $points, ?int $saleId = null, ?string $description = null, ?int $createdBy = null): self
    {
        $this->loyalty_points += $points;
        $this->total_points_earned += $points;
        $this->updateTier();
        $this->save();

        $this->loyaltyTransactions()->create([
            'sale_id' => $saleId,
            'type' => 'earn',
            'points' => $points,
            'points_balance_after' => $this->loyalty_points,
            'description' => $description ?? "Points earned from sale #{$saleId}",
            'created_by' => $createdBy,
        ]);

        return $this;
    }

    public function redeemPoints(int $points, int $discountAmount, ?int $saleId = null, ?string $description = null, ?int $createdBy = null): bool
    {
        if ($this->loyalty_points < $points) {
            return false;
        }

        $this->loyalty_points -= $points;
        $this->updateTier();
        $this->save();

        $this->loyaltyTransactions()->create([
            'sale_id' => $saleId,
            'type' => 'redeem',
            'points' => -$points,
            'points_balance_after' => $this->loyalty_points,
            'description' => $description ?? "Points redeemed for RM{$discountAmount} discount on sale #{$saleId}",
            'created_by' => $createdBy,
        ]);

        return true;
    }

    public function updateTier(): void
    {
        $totalPoints = $this->total_points_earned;
        $rates = config('services.loyalty.points_value', [
            'bronze' => 0.025,
            'silver' => 0.030,
            'gold' => 0.035,
            'platinum' => 0.040,
        ]);

        if ($totalPoints >= 10000) {
            $this->loyalty_tier = 'platinum';
        } elseif ($totalPoints >= 5000) {
            $this->loyalty_tier = 'gold';
        } elseif ($totalPoints >= 1000) {
            $this->loyalty_tier = 'silver';
        } else {
            $this->loyalty_tier = 'bronze';
        }
    }

    public function getPointsValue(): float
    {
        $rates = config('services.loyalty.points_value', [
            'bronze' => 0.025,
            'silver' => 0.030,
            'gold' => 0.035,
            'platinum' => 0.040,
        ]);
        
        $rate = $rates[$this->loyalty_tier] ?? $rates['bronze'];
        return $this->loyalty_points * $rate;
    }
}
