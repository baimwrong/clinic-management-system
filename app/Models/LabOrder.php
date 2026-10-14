<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'consultation_id',
        'patient_id',
        'doctor_id',
        'lab_test_id',
        'order_date',
        'status',
        'result_value',
        'result_flag',
        'technician_notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'completed_at' => 'datetime',
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

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'ordered' => 'bg-info text-dark',
            'in_progress' => 'bg-warning text-dark',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getFlagBadgeClassAttribute(): string
    {
        return match ($this->result_flag) {
            'critical' => 'bg-danger',
            'abnormal' => 'bg-warning text-dark',
            'normal' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $last = self::where('order_number', 'LIKE', "LBO-{$year}-%")
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/LBO-\d{4}-(\d+)/', $last->order_number, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return sprintf('LBO-%s-%04d', $year, $number);
    }
}
