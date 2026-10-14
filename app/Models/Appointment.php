<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'patient_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'type',
        'priority',
        'status',
        'reason',
        'notes',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'bg-primary',
            'checked_in' => 'bg-info text-dark',
            'in_consultation' => 'bg-warning text-dark',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            'no_show' => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'emergency' => 'bg-danger',
            'urgent' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    public static function generateAppointmentNumber(): string
    {
        $year = date('Y');
        $last = self::where('appointment_number', 'LIKE', "APT-{$year}-%")
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/APT-\d{4}-(\d+)/', $last->appointment_number, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return sprintf('APT-%s-%04d', $year, $number);
    }
}
