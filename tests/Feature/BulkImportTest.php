<?php

namespace Tests\Feature;

use App\Models\BulkImport;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use App\Services\BulkImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_bulk_import_service(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $csvContent = "first_name,last_name,date_of_birth,gender,blood_group,phone,email,national_id,address,allergies,chronic_diseases,emergency_contact_name,emergency_contact_phone,emergency_contact_relation,insurance_provider,insurance_policy_number\n"
                    . "Jonathan,Test,1990-01-01,male,O+,+1-555-9988,jonathan.test@example.com,NID-12345,\"123 Test St\",Penicillin,None,Jane Test,+1-555-9987,Spouse,BlueCross,POL-123\n";

        $file = UploadedFile::fake()->createWithContent('patients.csv', $csvContent);

        $service = app(BulkImportService::class);
        $import = $service->importPatients($file, $admin);

        $this->assertEquals(1, $import->successful_rows);
        $this->assertEquals(0, $import->failed_rows);
        $this->assertEquals('completed', $import->status);

        $this->assertDatabaseHas('patients', [
            'first_name' => 'Jonathan',
            'last_name' => 'Test',
            'email' => 'jonathan.test@example.com',
        ]);
    }

    public function test_medicine_bulk_import_service(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $csvContent = "code,name,generic_name,category,unit,dosage_form,manufacturer,unit_cost,selling_price,reorder_level,batch_number,expiry_date,quantity,supplier\n"
                    . "MED-9999,Test Cillin 500mg,Testcillin,Antibiotic,Capsule,500mg,Test Labs,0.20,0.50,20,BAT-TEST-001,2028-12-31,100,Test Supplier\n";

        $file = UploadedFile::fake()->createWithContent('medicines.csv', $csvContent);

        $service = app(BulkImportService::class);
        $import = $service->importMedicines($file, $admin);

        $this->assertEquals(1, $import->successful_rows);
        $this->assertEquals(0, $import->failed_rows);

        $this->assertDatabaseHas('medicines', [
            'code' => 'MED-9999',
            'name' => 'Test Cillin 500mg',
        ]);

        $this->assertDatabaseHas('medicine_batches', [
            'batch_number' => 'BAT-TEST-001',
            'quantity' => 100,
        ]);
    }
}
