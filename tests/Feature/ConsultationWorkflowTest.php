<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $doctorUser;

    private Doctor $doctor;

    private Patient $patient;

    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctorUser = User::factory()->create(['role' => 'doctor']);
        $this->doctor = Doctor::create([
            'user_id' => $this->doctorUser->id,
            'staff_id' => 'DOC-004',
            'license_number' => 'LIC-DOC-004',
            'specialization' => 'Internal Medicine',
            'consultation_fee' => 75.00,
            'status' => 'active',
        ]);

        $this->patient = Patient::create([
            'patient_code' => 'PAT-3001',
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'date_of_birth' => '1985-05-12',
            'gender' => 'female',
            'phone' => '8889990000',
        ]);

        $this->appointment = Appointment::create([
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '11:00:00',
            'status' => 'confirmed',
            'reason_for_visit' => 'Annual physical checkup',
        ]);
    }

    public function test_viewing_consultation_form_does_not_mutate_appointment_status(): void
    {
        $this->assertEquals('confirmed', $this->appointment->status);

        $response = $this->actingAs($this->doctorUser)->get("/consultations/create?appointment_id={$this->appointment->id}");

        $response->assertStatus(200);

        // Appointment status must remain 'confirmed' (no side effect on GET)
        $this->appointment->refresh();
        $this->assertEquals('confirmed', $this->appointment->status);
    }

    public function test_can_complete_consultation_without_prescriptions(): void
    {
        $response = $this->actingAs($this->doctorUser)->post('/consultations', [
            'appointment_id' => $this->appointment->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'consultation_date' => Carbon::today()->toDateString(),
            'chief_complaint' => 'Mild seasonal allergy symptoms',
            'history_of_present_illness' => 'Sneezing since yesterday',
            'physical_examination' => 'Clear chest, no fever',
            'primary_diagnosis' => 'Allergic rhinitis',
            'treatment_plan' => 'Advised rest and hydration',
            // No medicines provided (empty array)
            'medicines' => [],
        ]);

        $response->assertRedirect();

        // Appointment is now completed
        $this->appointment->refresh();
        $this->assertEquals('completed', $this->appointment->status);

        // Consultation was created
        $this->assertDatabaseHas('consultations', [
            'patient_id' => $this->patient->id,
            'primary_diagnosis' => 'Allergic rhinitis',
        ]);

        // Invoice was created for consultation fee ($75.00)
        $this->assertDatabaseHas('invoices', [
            'patient_id' => $this->patient->id,
            'total_amount' => 75.00,
        ]);
    }
}
