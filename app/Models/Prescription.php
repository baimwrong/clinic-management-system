<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_number',
        'consultation_id',
        'patient_id',
        'doctor_id',
        'status',
        'dispensed_by',
        'dispensed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dispensed_at' => 'datetime',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function dispensedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning text-dark',
            'dispensed' => 'bg-success',
            'partially_dispensed' => 'bg-info text-dark',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public static function generatePrescriptionNumber(): string
    {
        $year = date('Y');
        $last = self::where('prescription_number', 'LIKE', "RX-{$year}-%")
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/RX-\d{4}-(\d+)/', $last->prescription_number, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return sprintf('RX-%s-%04d', $year, $number);
    }
}
