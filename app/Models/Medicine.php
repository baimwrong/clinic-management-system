<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'generic_name',
        'category',
        'unit',
        'dosage_form',
        'manufacturer',
        'unit_cost',
        'selling_price',
        'reorder_level',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'reorder_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function getTotalStockAttribute(): int
    {
        if (isset($this->attributes['unexpired_stock'])) {
            return (int) $this->attributes['unexpired_stock'];
        }

        if ($this->relationLoaded('batches')) {
            return (int) $this->batches
                ->filter(fn ($batch) => $batch->expiry_date > now())
                ->sum('remaining_quantity');
        }

        return (int) $this->batches()
            ->where('expiry_date', '>', now())
            ->sum('remaining_quantity');
    }

    public function isLowStock(): bool
    {
        return $this->total_stock <= $this->reorder_level;
    }

    public static function generateMedicineCode(): string
    {
        $last = self::orderByDesc('id')->first();
        $number = $last ? ($last->id + 1) : 1;

        return sprintf('MED-%04d', $number);
    }
}
