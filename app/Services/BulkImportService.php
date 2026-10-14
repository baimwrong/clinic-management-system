<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BulkImport;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportService
{
    /**
     * Parse uploaded CSV file into associative array.
     */
    public function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new \RuntimeException('Failed to open uploaded file.');
        }

        $rows = [];
        $header = null;

        while (($data = fgetcsv($handle, 4096, ',')) !== false) {
            // Remove UTF-8 BOM if present on first header column
            if ($header === null) {
                if (isset($data[0])) {
                    $data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
                }
                $header = array_map(fn($item) => strtolower(trim($item)), $data);
                continue;
            }

            // Skip completely empty lines
            if (empty(array_filter($data, fn($v) => trim($v) !== ''))) {
                continue;
            }

            // Pad row if shorter than header
            if (count($data) < count($header)) {
                $data = array_pad($data, count($header), '');
            }

            $rowAssoc = [];
            foreach ($header as $index => $colName) {
                $rowAssoc[$colName] = isset($data[$index]) ? trim($data[$index]) : '';
            }

            $rows[] = $rowAssoc;
        }

        fclose($handle);

        return [
            'header' => $header ?? [],
            'rows' => $rows,
        ];
    }

    /**
     * Bulk Import Patients.
     */
    public function importPatients(UploadedFile $file, User $admin): BulkImport
    {
        $parsed = $this->parseCsv($file);
        $rows = $parsed['rows'];
        $totalRows = count($rows);
        $successCount = 0;
        $failedCount = 0;
        $errorLog = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // Line 1 is header

                $validator = Validator::make($row, [
                    'first_name' => 'required|string|max:100',
                    'last_name' => 'required|string|max:100',
                    'date_of_birth' => 'required|date_format:Y-m-d',
                    'gender' => 'required|in:male,female,other',
                    'phone' => 'required|string|max:30',
                    'email' => 'nullable|email|max:150',
                    'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-,Unknown',
                    'national_id' => 'nullable|string|max:100',
                ]);

                if ($validator->fails()) {
                    $failedCount++;
                    $errorLog[] = [
                        'row' => $rowNumber,
                        'name' => ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''),
                        'errors' => $validator->errors()->all(),
                    ];
                    continue;
                }

                Patient::create([
                    'patient_code' => Patient::generatePatientCode(),
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'date_of_birth' => $row['date_of_birth'],
                    'gender' => strtolower($row['gender']),
                    'blood_group' => $row['blood_group'] ?? 'Unknown',
                    'phone' => $row['phone'],
                    'email' => !empty($row['email']) ? $row['email'] : null,
                    'national_id' => !empty($row['national_id']) ? $row['national_id'] : null,
                    'address' => $row['address'] ?? null,
                    'emergency_contact_name' => $row['emergency_contact_name'] ?? null,
                    'emergency_contact_phone' => $row['emergency_contact_phone'] ?? null,
                    'emergency_contact_relation' => $row['emergency_contact_relation'] ?? null,
                    'allergies' => $row['allergies'] ?? null,
                    'chronic_diseases' => $row['chronic_diseases'] ?? null,
                    'insurance_provider' => $row['insurance_provider'] ?? null,
                    'insurance_policy_number' => $row['insurance_policy_number'] ?? null,
                ]);

                $successCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $status = $failedCount === 0 ? 'completed' : ($successCount > 0 ? 'completed_with_errors' : 'failed');

        $import = BulkImport::create([
            'admin_id' => $admin->id,
            'import_type' => 'patients',
            'filename' => $file->getClientOriginalName(),
            'total_rows' => $totalRows,
            'successful_rows' => $successCount,
            'failed_rows' => $failedCount,
            'error_log' => $errorLog,
            'status' => $status,
        ]);

        AuditLog::log('bulk_import.patients', "Imported {$successCount} patients with {$failedCount} errors.", [
            'import_id' => $import->id,
            'filename' => $import->filename,
        ], $admin);

        return $import;
    }

    /**
     * Bulk Import Medicines and Stock Batches.
     */
    public function importMedicines(UploadedFile $file, User $admin): BulkImport
    {
        $parsed = $this->parseCsv($file);
        $rows = $parsed['rows'];
        $totalRows = count($rows);
        $successCount = 0;
        $failedCount = 0;
        $errorLog = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                $validator = Validator::make($row, [
                    'name' => 'required|string|max:150',
                    'category' => 'required|string|max:100',
                    'unit' => 'required|string|max:50',
                    'unit_cost' => 'nullable|numeric|min:0',
                    'selling_price' => 'required|numeric|min:0',
                    'reorder_level' => 'nullable|integer|min:0',
                    'batch_number' => 'nullable|string|max:100',
                    'expiry_date' => 'nullable|date_format:Y-m-d',
                    'quantity' => 'nullable|integer|min:0',
                ]);

                if ($validator->fails()) {
                    $failedCount++;
                    $errorLog[] = [
                        'row' => $rowNumber,
                        'name' => $row['name'] ?? 'Row ' . $rowNumber,
                        'errors' => $validator->errors()->all(),
                    ];
                    continue;
                }

                $code = !empty($row['code']) ? $row['code'] : Medicine::generateMedicineCode();

                $medicine = Medicine::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $row['name'],
                        'generic_name' => $row['generic_name'] ?? null,
                        'category' => $row['category'],
                        'unit' => $row['unit'],
                        'dosage_form' => $row['dosage_form'] ?? null,
                        'manufacturer' => $row['manufacturer'] ?? null,
                        'unit_cost' => !empty($row['unit_cost']) ? (float)$row['unit_cost'] : 0.00,
                        'selling_price' => (float)$row['selling_price'],
                        'reorder_level' => !empty($row['reorder_level']) ? (int)$row['reorder_level'] : 10,
                        'is_active' => true,
                    ]
                );

                // Create initial batch if batch information is provided
                if (!empty($row['batch_number']) && !empty($row['expiry_date']) && !empty($row['quantity'])) {
                    MedicineBatch::create([
                        'medicine_id' => $medicine->id,
                        'batch_number' => $row['batch_number'],
                        'quantity' => (int)$row['quantity'],
                        'remaining_quantity' => (int)$row['quantity'],
                        'purchase_date' => now()->toDateString(),
                        'expiry_date' => $row['expiry_date'],
                        'supplier' => $row['supplier'] ?? 'Direct Supplier',
                    ]);
                }

                $successCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $status = $failedCount === 0 ? 'completed' : ($successCount > 0 ? 'completed_with_errors' : 'failed');

        $import = BulkImport::create([
            'admin_id' => $admin->id,
            'import_type' => 'medicines',
            'filename' => $file->getClientOriginalName(),
            'total_rows' => $totalRows,
            'successful_rows' => $successCount,
            'failed_rows' => $failedCount,
            'error_log' => $errorLog,
            'status' => $status,
        ]);

        AuditLog::log('bulk_import.medicines', "Imported {$successCount} medicines with {$failedCount} errors.", [
            'import_id' => $import->id,
            'filename' => $import->filename,
        ], $admin);

        return $import;
    }

    /**
     * Bulk Import Staff / Doctors.
     */
    public function importStaff(UploadedFile $file, User $admin): BulkImport
    {
        $parsed = $this->parseCsv($file);
        $rows = $parsed['rows'];
        $totalRows = count($rows);
        $successCount = 0;
        $failedCount = 0;
        $errorLog = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                $validator = Validator::make($row, [
                    'name' => 'required|string|max:150',
                    'email' => 'required|email|unique:users,email',
                    'role' => 'required|in:admin,doctor,nurse,pharmacist,receptionist',
                    'phone' => 'nullable|string|max:30',
                    'password' => 'nullable|min:6',
                    'specialization' => 'nullable|string|max:100',
                    'license_number' => 'nullable|string|max:100',
                    'consultation_fee' => 'nullable|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    $failedCount++;
                    $errorLog[] = [
                        'row' => $rowNumber,
                        'name' => $row['name'] ?? 'Row ' . $rowNumber,
                        'errors' => $validator->errors()->all(),
                    ];
                    continue;
                }

                $user = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'role' => strtolower($row['role']),
                    'phone' => $row['phone'] ?? null,
                    'password' => Hash::make(!empty($row['password']) ? $row['password'] : 'password123'),
                    'status' => 'active',
                ]);

                if ($user->role === 'doctor') {
                    $staffId = 'DOC-' . sprintf('%03d', $user->id);
                    $license = !empty($row['license_number']) ? $row['license_number'] : 'LIC-' . strtoupper(substr(md5(uniqid()), 0, 8));

                    Doctor::create([
                        'user_id' => $user->id,
                        'staff_id' => $staffId,
                        'specialization' => $row['specialization'] ?? 'General Practice',
                        'license_number' => $license,
                        'qualification' => $row['qualification'] ?? 'MD',
                        'consultation_fee' => !empty($row['consultation_fee']) ? (float)$row['consultation_fee'] : 50.00,
                        'room_number' => $row['room_number'] ?? '101',
                        'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                        'available_time_start' => '08:00:00',
                        'available_time_end' => '17:00:00',
                        'status' => 'active',
                    ]);
                }

                $successCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $status = $failedCount === 0 ? 'completed' : ($successCount > 0 ? 'completed_with_errors' : 'failed');

        $import = BulkImport::create([
            'admin_id' => $admin->id,
            'import_type' => 'staff',
            'filename' => $file->getClientOriginalName(),
            'total_rows' => $totalRows,
            'successful_rows' => $successCount,
            'failed_rows' => $failedCount,
            'error_log' => $errorLog,
            'status' => $status,
        ]);

        AuditLog::log('bulk_import.staff', "Imported {$successCount} staff accounts with {$failedCount} errors.", [
            'import_id' => $import->id,
            'filename' => $import->filename,
        ], $admin);

        return $import;
    }

    /**
     * Download sample CSV templates.
     */
    public function downloadTemplate(string $type): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"sample_{$type}_import_template.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($type) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            switch ($type) {
                case 'patients':
                    fputcsv($handle, [
                        'first_name',
                        'last_name',
                        'date_of_birth',
                        'gender',
                        'blood_group',
                        'phone',
                        'email',
                        'national_id',
                        'address',
                        'allergies',
                        'chronic_diseases',
                        'emergency_contact_name',
                        'emergency_contact_phone',
                        'emergency_contact_relation',
                        'insurance_provider',
                        'insurance_policy_number',
                    ]);
                    fputcsv($handle, [
                        'John',
                        'Doe',
                        '1988-04-12',
                        'male',
                        'O+',
                        '+1-555-0199',
                        'john.doe@example.com',
                        'ID-98765432',
                        '742 Evergreen Terrace, Springfield',
                        'Penicillin, Aspirin',
                        'Hypertension',
                        'Jane Doe',
                        '+1-555-0198',
                        'Spouse',
                        'BlueShield Healthcare',
                        'POL-55443322',
                    ]);
                    fputcsv($handle, [
                        'Sarah',
                        'Connor',
                        '1992-09-25',
                        'female',
                        'A+',
                        '+1-555-0145',
                        'sarah.connor@example.com',
                        'ID-11223344',
                        '405 Cypress Ave, Metro City',
                        'None',
                        'None',
                        'Kyle Reese',
                        '+1-555-0146',
                        'Brother',
                        'Aetna Global',
                        'POL-99887766',
                    ]);
                    break;

                case 'medicines':
                    fputcsv($handle, [
                        'code',
                        'name',
                        'generic_name',
                        'category',
                        'unit',
                        'dosage_form',
                        'manufacturer',
                        'unit_cost',
                        'selling_price',
                        'reorder_level',
                        'batch_number',
                        'expiry_date',
                        'quantity',
                        'supplier',
                    ]);
                    fputcsv($handle, [
                        'MED-0001',
                        'Amoxicillin 500mg',
                        'Amoxicillin Trihydrate',
                        'Antibiotic',
                        'Capsule',
                        '500mg',
                        'PharmaCare Labs',
                        '0.25',
                        '0.60',
                        '50',
                        'BAT-AMX-2026-A',
                        '2028-12-31',
                        '500',
                        'Global Health Meds Ltd',
                    ]);
                    fputcsv($handle, [
                        'MED-0002',
                        'Paracetamol 500mg',
                        'Acetaminophen',
                        'Analgesic',
                        'Tablet',
                        '500mg',
                        'MediHealth Corp',
                        '0.05',
                        '0.20',
                        '100',
                        'BAT-PCM-2026-B',
                        '2028-06-30',
                        '1000',
                        'Prime Pharma Supply',
                    ]);
                    break;

                case 'staff':
                    fputcsv($handle, [
                        'name',
                        'email',
                        'role',
                        'phone',
                        'password',
                        'specialization',
                        'license_number',
                        'qualification',
                        'consultation_fee',
                        'room_number',
                    ]);
                    fputcsv($handle, [
                        'Dr. Emily Watson',
                        'emily.watson@clinic.local',
                        'doctor',
                        '+1-555-0811',
                        'password123',
                        'Cardiology',
                        'MD-LIC-445566',
                        'MD, FACC',
                        '85.00',
                        'Room 204',
                    ]);
                    fputcsv($handle, [
                        'Nurse Amanda Clark',
                        'amanda.clark@clinic.local',
                        'nurse',
                        '+1-555-0822',
                        'password123',
                        '',
                        '',
                        'BSN, RN',
                        '0.00',
                        'Triage Station A',
                    ]);
                    fputcsv($handle, [
                        'Robert Davis',
                        'robert.davis@clinic.local',
                        'pharmacist',
                        '+1-555-0833',
                        'password123',
                        '',
                        '',
                        'PharmD',
                        '0.00',
                        'Pharmacy Counter',
                    ]);
                    break;
            }

            fclose($handle);
        }, 200, $headers);
    }
}
