<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_cannot_access_audit_logs(): void
    {
        $patientUser = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($patientUser)->get('/admin/audit-logs');

        $response->assertStatus(403);
    }

    public function test_patient_cannot_access_doctor_creation(): void
    {
        $patientUser = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($patientUser)->get('/doctors/create');

        $response->assertStatus(403);
    }

    public function test_patient_cannot_view_another_patients_profile(): void
    {
        $patientUser1 = User::factory()->create(['role' => 'patient']);
        $patient1 = Patient::create([
            'patient_code' => 'PAT-1001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone' => '1234567890',
            'user_id' => $patientUser1->id,
        ]);

        $patientUser2 = User::factory()->create(['role' => 'patient']);
        $patient2 = Patient::create([
            'patient_code' => 'PAT-1002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'date_of_birth' => '1992-02-02',
            'gender' => 'female',
            'phone' => '0987654321',
            'user_id' => $patientUser2->id,
        ]);

        // Patient 1 views Patient 2's record
        $response = $this->actingAs($patientUser1)->get("/patients/{$patient2->id}");

        $response->assertStatus(403);
    }

    public function test_admin_has_access_to_admin_and_clinical_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/audit-logs')->assertStatus(200);
        $this->actingAs($admin)->get('/doctors')->assertStatus(200);
        $this->actingAs($admin)->get('/billing')->assertStatus(200);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be throttled (HTTP 429)
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }
}
