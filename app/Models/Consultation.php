<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_number',
        'appointment_id',
        'patient_id',
        'doctor_id',
        'consultation_date',
        'chief_complaint',
        'history_of_present_illness',
        'physical_examination',
        'primary_diagnosis',
        'icd10_code',
        'secondary_diagnosis',
        'treatment_plan',
        'doctor_notes',
        'follow_up_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'datetime',
            'follow_up_date' => 'date',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class);
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public static function generateConsultationNumber(): string
    {
        $year = date('Y');
        $last = self::where('consultation_number', 'LIKE', "CNS-{$year}-%")
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/CNS-\d{4}-(\d+)/', $last->consultation_number, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return sprintf('CNS-%s-%04d', $year, $number);
    }
}
