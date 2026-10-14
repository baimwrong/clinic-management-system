<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'consultation_id',
        'recorded_by',
        'recorded_at',
        'systolic_bp',
        'diastolic_bp',
        'pulse_rate',
        'temperature',
        'respiratory_rate',
        'oxygen_saturation',
        'height_cm',
        'weight_kg',
        'bmi',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'temperature' => 'decimal:1',
            'oxygen_saturation' => 'decimal:1',
            'height_cm' => 'decimal:1',
            'weight_kg' => 'decimal:1',
            'bmi' => 'decimal:1',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getBloodPressureAttribute(): ?string
    {
        if ($this->systolic_bp && $this->diastolic_bp) {
            return "{$this->systolic_bp}/{$this->diastolic_bp} mmHg";
        }
        return null;
    }

    public static function calculateBmi(?float $weightKg, ?float $heightCm): ?float
    {
        if ($weightKg > 0 && $heightCm > 0) {
            $heightM = $heightCm / 100;
            return round($weightKg / ($heightM * $heightM), 1);
        }
        return null;
    }
}
