<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'staff_id',
        'specialization',
        'license_number',
        'qualification',
        'consultation_fee',
        'room_number',
        'bio',
        'available_days',
        'available_time_start',
        'available_time_end',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'available_days' => 'array',
            'consultation_fee' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class);
    }

    public function getNameAttribute(): string
    {
        return 'Dr. ' . ($this->user?->name ?? 'Unknown');
    }
}
