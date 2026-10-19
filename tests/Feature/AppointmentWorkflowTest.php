<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_cannot_double_book_overlapping_appointment_for_same_doctor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'staff_id' => 'DOC-001',
            'license_number' => 'LIC-DOC-001',
            'specialization' => 'General Medicine',
            'qualification' => 'MBBS',
            'consultation_fee' => 50.00,
            'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'shift_start' => '08:00',
            'shift_end' => '17:00',
            'status' => 'active',
        ]);

        $patient1 = Patient::create([
            'patient_code' => 'PAT-0001',
            'first_name' => 'Alice',
            'last_name' => 'Wonder',
            'date_of_birth' => '1995-05-15',
            'gender' => 'female',
            'phone' => '1112223333',
        ]);

        $patient2 = Patient::create([
            'patient_code' => 'PAT-0002',
            'first_name' => 'Bob',
            'last_name' => 'Builder',
            'date_of_birth' => '1992-03-10',
            'gender' => 'male',
            'phone' => '4445556666',
        ]);

        // Next Monday date
        $appointmentDate = Carbon::now()->next(Carbon::MONDAY)->toDateString();
        $appointmentTime = '10:00:00';

        // First booking succeeds
        Appointment::create([
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'status' => 'scheduled',
            'reason_for_visit' => 'Regular checkup',
        ]);

        // Attempt second booking for same doctor at same date and time
        $response = $this->actingAs($admin)->post('/appointments', [
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => '10:00',
            'type' => 'routine',
            'priority' => 'normal',
            'reason_for_visit' => 'Flu symptoms',
        ]);

        $response->assertSessionHasErrors('appointment_time');
        $this->assertEquals(1, Appointment::where('doctor_id', $doctor->id)->count());
    }

    public function test_cannot_book_inactive_doctor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'staff_id' => 'DOC-002',
            'license_number' => 'LIC-DOC-002',
            'specialization' => 'Cardiology',
            'qualification' => 'MD',
            'consultation_fee' => 100.00,
            'available_days' => ['Monday', 'Tuesday'],
            'shift_start' => '09:00',
            'shift_end' => '15:00',
            'status' => 'inactive',
        ]);

        $patient = Patient::create([
            'patient_code' => 'PAT-0003',
            'first_name' => 'Charlie',
            'last_name' => 'Day',
            'date_of_birth' => '1988-08-08',
            'gender' => 'male',
            'phone' => '7778889999',
        ]);

        $date = Carbon::now()->next(Carbon::MONDAY)->toDateString();

        $response = $this->actingAs($admin)->post('/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'appointment_time' => '10:00',
            'type' => 'routine',
            'priority' => 'normal',
            'reason_for_visit' => 'Chest consultation',
        ]);

        $response->assertSessionHasErrors('doctor_id');
        $this->assertEquals(0, Appointment::count());
    }
}
