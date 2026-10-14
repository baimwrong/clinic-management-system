<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Models\VitalSign;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with complete clinic records.
     */
    public function run(): void
    {
        // 1. Core Users with Different Roles
        $admin = User::firstOrCreate(
            ['email' => 'admin@clinic.local'],
            [
                'name' => 'Dr. Eleanor Sterling',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '+1-555-0100',
                'status' => 'active',
            ]
        );

        $docUser1 = User::firstOrCreate(
            ['email' => 'doctor@clinic.local'],
            [
                'name' => 'Marcus Vance',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'phone' => '+1-555-0101',
                'status' => 'active',
            ]
        );

        $docUser2 = User::firstOrCreate(
            ['email' => 'dr.sarah@clinic.local'],
            [
                'name' => 'Sarah Jenkins',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'phone' => '+1-555-0102',
                'status' => 'active',
            ]
        );

        $docUser3 = User::firstOrCreate(
            ['email' => 'dr.robert@clinic.local'],
            [
                'name' => 'Robert Chen',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'phone' => '+1-555-0103',
                'status' => 'active',
            ]
        );

        $nurseUser = User::firstOrCreate(
            ['email' => 'nurse@clinic.local'],
            [
                'name' => 'Clara Barton',
                'password' => Hash::make('password123'),
                'role' => 'nurse',
                'phone' => '+1-555-0104',
                'status' => 'active',
            ]
        );

        $pharmacistUser = User::firstOrCreate(
            ['email' => 'pharmacist@clinic.local'],
            [
                'name' => 'Walter Reed',
                'password' => Hash::make('password123'),
                'role' => 'pharmacist',
                'phone' => '+1-555-0105',
                'status' => 'active',
            ]
        );

        $receptionistUser = User::firstOrCreate(
            ['email' => 'receptionist@clinic.local'],
            [
                'name' => 'Alice Cooper',
                'password' => Hash::make('password123'),
                'role' => 'receptionist',
                'phone' => '+1-555-0106',
                'status' => 'active',
            ]
        );

        $patientUser = User::firstOrCreate(
            ['email' => 'patient@clinic.local'],
            [
                'name' => 'Johnathan Doe',
                'password' => Hash::make('password123'),
                'role' => 'patient',
                'phone' => '+1-555-0107',
                'status' => 'active',
            ]
        );

        // 2. Doctor Profiles
        $doc1 = Doctor::firstOrCreate(
            ['user_id' => $docUser1->id],
            [
                'staff_id' => 'DOC-0001',
                'specialization' => 'Cardiology',
                'license_number' => 'MD-CAR-8821',
                'qualification' => 'MD, FACC, Harvard Medical School',
                'consultation_fee' => 75.00,
                'room_number' => 'Room 201 (Cardio Suite)',
                'bio' => 'Consultant Cardiologist with 15 years experience in non-invasive clinical cardiology and cardiovascular risk management.',
                'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'available_time_start' => '08:30:00',
                'available_time_end' => '16:30:00',
                'status' => 'active',
            ]
        );

        $doc2 = Doctor::firstOrCreate(
            ['user_id' => $docUser2->id],
            [
                'staff_id' => 'DOC-0002',
                'specialization' => 'Pediatrics',
                'license_number' => 'MD-PED-4412',
                'qualification' => 'MD, FAAP, Johns Hopkins',
                'consultation_fee' => 60.00,
                'room_number' => 'Room 105 (Pediatric Care)',
                'bio' => 'Specializing in neonatal care, infant nutrition, childhood developmental screening, and pediatric preventative healthcare.',
                'available_days' => ['Monday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'available_time_start' => '09:00:00',
                'available_time_end' => '17:00:00',
                'status' => 'active',
            ]
        );

        $doc3 = Doctor::firstOrCreate(
            ['user_id' => $docUser3->id],
            [
                'staff_id' => 'DOC-0003',
                'specialization' => 'Internal Medicine',
                'license_number' => 'MD-INT-9923',
                'qualification' => 'MD, FACP, Mayo Clinic',
                'consultation_fee' => 65.00,
                'room_number' => 'Room 102 (General Medicine)',
                'bio' => 'Internal Medicine physician dedicated to chronic illness management, metabolic disorders, hypertension, and preventive clinical care.',
                'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'available_time_start' => '08:00:00',
                'available_time_end' => '16:00:00',
                'status' => 'active',
            ]
        );

        // 3. Patients
        $patientsData = [
            [
                'patient_code' => 'PAT-2026-0001',
                'user_id' => $patientUser->id,
                'first_name' => 'Johnathan',
                'last_name' => 'Doe',
                'date_of_birth' => '1985-05-14',
                'gender' => 'male',
                'blood_group' => 'O+',
                'phone' => '+1-555-0107',
                'email' => 'patient@clinic.local',
                'national_id' => 'NID-99220011',
                'address' => '742 Evergreen Terrace, Sector 4',
                'emergency_contact_name' => 'Mary Doe',
                'emergency_contact_relation' => 'Spouse',
                'emergency_contact_phone' => '+1-555-0108',
                'allergies' => 'Penicillin, Cefaclor',
                'chronic_diseases' => 'Essential Hypertension',
                'insurance_provider' => 'BlueCross BlueShield',
                'insurance_policy_number' => 'BC-88992211',
            ],
            [
                'patient_code' => 'PAT-2026-0002',
                'user_id' => null,
                'first_name' => 'Emma',
                'last_name' => 'Watson',
                'date_of_birth' => '1994-08-22',
                'gender' => 'female',
                'blood_group' => 'A+',
                'phone' => '+1-555-0211',
                'email' => 'emma.w@example.com',
                'national_id' => 'NID-33445566',
                'address' => '12 Rosewood Lane, Metro City',
                'emergency_contact_name' => 'Thomas Watson',
                'emergency_contact_relation' => 'Father',
                'emergency_contact_phone' => '+1-555-0212',
                'allergies' => 'None',
                'chronic_diseases' => 'None',
                'insurance_provider' => 'Aetna Healthcare',
                'insurance_policy_number' => 'AET-112233',
            ],
            [
                'patient_code' => 'PAT-2026-0003',
                'user_id' => null,
                'first_name' => 'David',
                'last_name' => 'Miller',
                'date_of_birth' => '1972-11-03',
                'gender' => 'male',
                'blood_group' => 'B+',
                'phone' => '+1-555-0322',
                'email' => 'david.miller@example.com',
                'national_id' => 'NID-77889900',
                'address' => '88 High Street, Suite 4B',
                'emergency_contact_name' => 'Susan Miller',
                'emergency_contact_relation' => 'Spouse',
                'emergency_contact_phone' => '+1-555-0323',
                'allergies' => 'Aspirin, NSAIDs',
                'chronic_diseases' => 'Type 2 Diabetes Mellitus, Hyperlipidemia',
                'insurance_provider' => 'Cigna Global Health',
                'insurance_policy_number' => 'CG-44556677',
            ],
            [
                'patient_code' => 'PAT-2026-0004',
                'user_id' => null,
                'first_name' => 'Sophia',
                'last_name' => 'Martinez',
                'date_of_birth' => '2018-03-19',
                'gender' => 'female',
                'blood_group' => 'O-',
                'phone' => '+1-555-0433',
                'email' => 'martinez.fam@example.com',
                'national_id' => 'NID-55667788',
                'address' => '250 Oakwood Drive',
                'emergency_contact_name' => 'Carlos Martinez',
                'emergency_contact_relation' => 'Father',
                'emergency_contact_phone' => '+1-555-0434',
                'allergies' => 'Peanuts, Egg Protein',
                'chronic_diseases' => 'Mild Childhood Asthma',
                'insurance_provider' => 'UnitedHealthcare',
                'insurance_policy_number' => 'UHC-990011',
            ],
            [
                'patient_code' => 'PAT-2026-0005',
                'user_id' => null,
                'first_name' => 'James',
                'last_name' => 'Wilson',
                'date_of_birth' => '1960-12-30',
                'gender' => 'male',
                'blood_group' => 'AB+',
                'phone' => '+1-555-0544',
                'email' => 'james.wilson@example.com',
                'national_id' => 'NID-11229988',
                'address' => '314 Elm Street, Downtown',
                'emergency_contact_name' => 'Laura Wilson',
                'emergency_contact_relation' => 'Daughter',
                'emergency_contact_phone' => '+1-555-0545',
                'allergies' => 'Sulfa Antibiotics',
                'chronic_diseases' => 'Coronary Artery Disease, Osteoarthritis',
                'insurance_provider' => 'Medicare / Humana',
                'insurance_policy_number' => 'MED-776655',
            ],
        ];

        $patients = [];
        foreach ($patientsData as $pData) {
            $patients[] = Patient::firstOrCreate(['patient_code' => $pData['patient_code']], $pData);
        }

        // 4. Pharmacy Catalog (Medicines & Batches)
        $medicinesData = [
            [
                'code' => 'MED-0001',
                'name' => 'Amoxicillin 500mg',
                'generic_name' => 'Amoxicillin Trihydrate',
                'category' => 'Antibiotic',
                'unit' => 'Capsule',
                'dosage_form' => '500mg',
                'manufacturer' => 'PharmaCare Labs',
                'unit_cost' => 0.20,
                'selling_price' => 0.50,
                'reorder_level' => 50,
                'batches' => [
                    ['batch_number' => 'BAT-AMX-2026-A', 'quantity' => 500, 'remaining' => 420, 'expiry' => '2028-12-31'],
                ]
            ],
            [
                'code' => 'MED-0002',
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Acetaminophen',
                'category' => 'Analgesic',
                'unit' => 'Tablet',
                'dosage_form' => '500mg',
                'manufacturer' => 'GSK Health',
                'unit_cost' => 0.05,
                'selling_price' => 0.20,
                'reorder_level' => 100,
                'batches' => [
                    ['batch_number' => 'BAT-PCM-2026-01', 'quantity' => 1000, 'remaining' => 850, 'expiry' => '2028-06-30'],
                ]
            ],
            [
                'code' => 'MED-0003',
                'name' => 'Augmentin 625mg',
                'generic_name' => 'Amoxicillin + Clavulanate Potassium',
                'category' => 'Antibiotic',
                'unit' => 'Tablet',
                'dosage_form' => '625mg',
                'manufacturer' => 'GlaxoSmithKline',
                'unit_cost' => 1.20,
                'selling_price' => 2.50,
                'reorder_level' => 30,
                'batches' => [
                    ['batch_number' => 'BAT-AUG-2026-B', 'quantity' => 200, 'remaining' => 160, 'expiry' => '2027-10-31'],
                ]
            ],
            [
                'code' => 'MED-0004',
                'name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen',
                'category' => 'NSAID / Anti-inflammatory',
                'unit' => 'Tablet',
                'dosage_form' => '400mg',
                'manufacturer' => 'Pfizer Global',
                'unit_cost' => 0.10,
                'selling_price' => 0.35,
                'reorder_level' => 50,
                'batches' => [
                    ['batch_number' => 'BAT-IBU-2026-C', 'quantity' => 400, 'remaining' => 310, 'expiry' => '2028-04-15'],
                ]
            ],
            [
                'code' => 'MED-0005',
                'name' => 'Metformin 500mg',
                'generic_name' => 'Metformin Hydrochloride',
                'category' => 'Antidiabetic',
                'unit' => 'Tablet',
                'dosage_form' => '500mg',
                'manufacturer' => 'Merck Healthcare',
                'unit_cost' => 0.15,
                'selling_price' => 0.40,
                'reorder_level' => 40,
                'batches' => [
                    ['batch_number' => 'BAT-MET-2026-D', 'quantity' => 600, 'remaining' => 490, 'expiry' => '2028-09-30'],
                ]
            ],
            [
                'code' => 'MED-0006',
                'name' => 'Amlodipine 5mg',
                'generic_name' => 'Amlodipine Besylate',
                'category' => 'Antihypertensive',
                'unit' => 'Tablet',
                'dosage_form' => '5mg',
                'manufacturer' => 'Novartis Pharma',
                'unit_cost' => 0.12,
                'selling_price' => 0.35,
                'reorder_level' => 30,
                'batches' => [
                    ['batch_number' => 'BAT-AML-2026-E', 'quantity' => 300, 'remaining' => 240, 'expiry' => '2027-11-30'],
                ]
            ],
            [
                'code' => 'MED-0007',
                'name' => 'Omeprazole 20mg',
                'generic_name' => 'Omeprazole Delayed Release',
                'category' => 'Gastrointestinal',
                'unit' => 'Capsule',
                'dosage_form' => '20mg',
                'manufacturer' => 'AstraZeneca',
                'unit_cost' => 0.25,
                'selling_price' => 0.75,
                'reorder_level' => 30,
                'batches' => [
                    ['batch_number' => 'BAT-OMP-2026-F', 'quantity' => 300, 'remaining' => 15, 'expiry' => '2027-08-31'], // Intentionally low stock for alert!
                ]
            ],
            [
                'code' => 'MED-0008',
                'name' => 'Salbutamol Inhaler 100mcg',
                'generic_name' => 'Albuterol Sulfate',
                'category' => 'Respiratory / Bronchodilator',
                'unit' => 'Inhaler',
                'dosage_form' => '200 doses',
                'manufacturer' => 'Ventolin Labs',
                'unit_cost' => 4.50,
                'selling_price' => 12.00,
                'reorder_level' => 15,
                'batches' => [
                    ['batch_number' => 'BAT-SAL-2026-G', 'quantity' => 50, 'remaining' => 8, 'expiry' => '2027-06-30'], // Low stock!
                ]
            ],
        ];

        $medicines = [];
        foreach ($medicinesData as $m) {
            $batches = $m['batches'];
            unset($m['batches']);
            $medicine = Medicine::firstOrCreate(['code' => $m['code']], $m);

            foreach ($batches as $b) {
                MedicineBatch::firstOrCreate(
                    [
                        'medicine_id' => $medicine->id,
                        'batch_number' => $b['batch_number'],
                    ],
                    [
                        'quantity' => $b['quantity'],
                        'remaining_quantity' => $b['remaining'],
                        'purchase_date' => now()->subMonths(2)->toDateString(),
                        'expiry_date' => $b['expiry'],
                        'supplier' => 'MediSupply International',
                    ]
                );
            }
            $medicines[] = $medicine;
        }

        // 5. Diagnostic Laboratory Tests Catalog
        $labTestsData = [
            [
                'code' => 'LAB-CBC',
                'name' => 'Complete Blood Count (CBC) with Differential',
                'category' => 'Hematology',
                'price' => 25.00,
                'reference_range' => 'WBC: 4.5-11.0 x10^3/µL, RBC: 4.3-5.9 x10^6/µL, Hgb: 13.5-17.5 g/dL, Plt: 150-450 x10^3/µL',
                'description' => 'Comprehensive evaluation of cellular blood components including erythrocytes, leukocytes, and thrombocytes.',
            ],
            [
                'code' => 'LAB-LIPID',
                'name' => 'Comprehensive Lipid Profile Panel',
                'category' => 'Biochemistry',
                'price' => 35.00,
                'reference_range' => 'Total Cholesterol: <200 mg/dL, HDL: >40 mg/dL, LDL: <100 mg/dL, Triglycerides: <150 mg/dL',
                'description' => 'Evaluates cardiovascular risk assessing total cholesterol, high-density lipoproteins, low-density lipoproteins, and triglycerides.',
            ],
            [
                'code' => 'LAB-FBG',
                'name' => 'Fasting Blood Glucose (FBG)',
                'category' => 'Biochemistry',
                'price' => 15.00,
                'reference_range' => 'Normal: 70 - 99 mg/dL, Impaired: 100 - 125 mg/dL, Diabetic: ≥126 mg/dL',
                'description' => 'Measurement of blood glucose after an overnight fast to screen for diabetes mellitus.',
            ],
            [
                'code' => 'LAB-UA',
                'name' => 'Urinalysis Complete Routine',
                'category' => 'Microbiology & Chemistry',
                'price' => 18.00,
                'reference_range' => 'pH: 4.5-8.0, Specific Gravity: 1.005-1.030, Protein: Negative, Glucose: Negative, Ketones: Negative',
                'description' => 'Physical, chemical, and microscopic evaluation of urine for renal, metabolic, and urinary tract disorders.',
            ],
            [
                'code' => 'LAB-TSH',
                'name' => 'Thyroid Stimulating Hormone (TSH) Serum',
                'category' => 'Endocrinology',
                'price' => 32.00,
                'reference_range' => '0.40 - 4.00 mIU/L',
                'description' => 'Primary test for thyroid gland function and monitoring of thyroid hormone replacement therapy.',
            ],
        ];

        $labTests = [];
        foreach ($labTestsData as $lt) {
            $labTests[] = LabTest::firstOrCreate(['code' => $lt['code']], $lt);
        }

        // 6. Appointments
        $today = Carbon::today();
        $appointmentsData = [
            [
                'appointment_number' => 'APT-' . $today->year . '-0001',
                'patient_id' => $patients[0]->id,
                'doctor_id' => $doc1->id,
                'appointment_date' => $today->toDateString(),
                'appointment_time' => '09:00:00',
                'type' => 'consultation',
                'priority' => 'normal',
                'status' => 'completed',
                'reason' => 'Routine cardiology check-up for hypertension management.',
            ],
            [
                'appointment_number' => 'APT-' . $today->year . '-0002',
                'patient_id' => $patients[1]->id,
                'doctor_id' => $doc3->id,
                'appointment_date' => $today->toDateString(),
                'appointment_time' => '10:30:00',
                'type' => 'consultation',
                'priority' => 'urgent',
                'status' => 'in_consultation',
                'reason' => 'Severe acute migraine and photo-sensitivity.',
            ],
            [
                'appointment_number' => 'APT-' . $today->year . '-0003',
                'patient_id' => $patients[2]->id,
                'doctor_id' => $doc3->id,
                'appointment_date' => $today->toDateString(),
                'appointment_time' => '11:15:00',
                'type' => 'follow_up',
                'priority' => 'normal',
                'status' => 'checked_in',
                'reason' => 'Follow up on blood sugar control and HbA1c review.',
            ],
            [
                'appointment_number' => 'APT-' . $today->year . '-0004',
                'patient_id' => $patients[3]->id,
                'doctor_id' => $doc2->id,
                'appointment_date' => $today->toDateString(),
                'appointment_time' => '14:00:00',
                'type' => 'routine',
                'priority' => 'normal',
                'status' => 'scheduled',
                'reason' => 'Pediatric milestone check and seasonal flu vaccine.',
            ],
            [
                'appointment_number' => 'APT-' . $today->year . '-0005',
                'patient_id' => $patients[4]->id,
                'doctor_id' => $doc1->id,
                'appointment_date' => $today->toDateString(),
                'appointment_time' => '15:30:00',
                'type' => 'consultation',
                'priority' => 'urgent',
                'status' => 'scheduled',
                'reason' => 'Shortness of breath on mild exertion and peripheral ankle edema.',
            ],
        ];

        $appointments = [];
        foreach ($appointmentsData as $apt) {
            $appointments[] = Appointment::firstOrCreate(['appointment_number' => $apt['appointment_number']], $apt);
        }

        // 7. Completed Consultation for Patient 1 with Vitals, Prescription, Invoice, and Lab Order
        $cns = Consultation::firstOrCreate(
            ['consultation_number' => 'CNS-' . $today->year . '-0001'],
            [
                'appointment_id' => $appointments[0]->id,
                'patient_id' => $patients[0]->id,
                'doctor_id' => $doc1->id,
                'consultation_date' => $today->copy()->setTime(9, 30),
                'chief_complaint' => 'Patient presents for quarterly cardiovascular review. Reports mild intermittent morning headaches, denies chest discomfort or palpitations.',
                'history_of_present_illness' => 'Known hypertensive for 6 years on amlodipine. Adherence reported good. No nocturnal dyspnea.',
                'physical_examination' => 'Alert, oriented x3. Heart sounds S1, S2 regular, no murmurs. Lungs clear to auscultation bilaterally. No peripheral edema.',
                'primary_diagnosis' => 'Essential (Primary) Hypertension, Benign',
                'icd10_code' => 'I10',
                'secondary_diagnosis' => 'Mild tension cephalalgia',
                'treatment_plan' => 'Maintain Amlodipine 5mg daily. Low-sodium Mediterranean diet. Continue home BP logging.',
                'doctor_notes' => 'BP mildly elevated at 138/88 mmHg. Recheck in 3 months with home log.',
                'follow_up_date' => $today->copy()->addMonths(3)->toDateString(),
                'status' => 'completed',
            ]
        );

        // Vitals for this consultation
        VitalSign::firstOrCreate(
            ['consultation_id' => $cns->id],
            [
                'patient_id' => $patients[0]->id,
                'recorded_by' => $nurseUser->id,
                'recorded_at' => $today->copy()->setTime(9, 10),
                'systolic_bp' => 138,
                'diastolic_bp' => 88,
                'pulse_rate' => 74,
                'temperature' => 36.8,
                'respiratory_rate' => 16,
                'oxygen_saturation' => 99.0,
                'height_cm' => 178.0,
                'weight_kg' => 82.5,
                'bmi' => 26.0,
                'notes' => 'Patient rested seated 10 mins prior to automated cuff measurement.',
            ]
        );

        // Prescription for this consultation
        $rx = Prescription::firstOrCreate(
            ['prescription_number' => 'RX-' . $today->year . '-0001'],
            [
                'consultation_id' => $cns->id,
                'patient_id' => $patients[0]->id,
                'doctor_id' => $doc1->id,
                'status' => 'dispensed',
                'dispensed_by' => $pharmacistUser->id,
                'dispensed_at' => $today->copy()->setTime(10, 0),
                'notes' => 'Dispensed 30 tablets. Instructed patient on once-daily morning dosing.',
            ]
        );

        PrescriptionItem::firstOrCreate(
            [
                'prescription_id' => $rx->id,
                'medicine_id' => $medicines[5]->id, // Amlodipine
            ],
            [
                'dosage' => '5mg',
                'frequency' => 'Once daily in the morning',
                'duration_days' => 30,
                'quantity_prescribed' => 30,
                'quantity_dispensed' => 30,
                'instructions' => 'Take with water with or without food.',
            ]
        );

        // Lab Order for this consultation
        $lbo = LabOrder::firstOrCreate(
            ['order_number' => 'LBO-' . $today->year . '-0001'],
            [
                'consultation_id' => $cns->id,
                'patient_id' => $patients[0]->id,
                'doctor_id' => $doc1->id,
                'lab_test_id' => $labTests[1]->id, // Lipid Panel
                'order_date' => $today->copy()->setTime(9, 45),
                'status' => 'completed',
                'result_value' => 'Total Cholesterol: 188 mg/dL | HDL: 48 mg/dL | LDL: 98 mg/dL | Triglycerides: 135 mg/dL',
                'result_flag' => 'normal',
                'technician_notes' => 'Fasting status confirmed 12 hours. Specimen clear, non-lipemic.',
                'completed_at' => $today->copy()->setTime(11, 30),
            ]
        );

        // Invoice for this consultation
        $inv = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-' . $today->year . '-0001'],
            [
                'patient_id' => $patients[0]->id,
                'consultation_id' => $cns->id,
                'subtotal' => 120.50,
                'tax_amount' => 0.00,
                'discount_amount' => 10.00,
                'total_amount' => 110.50,
                'paid_amount' => 110.50,
                'balance_due' => 0.00,
                'payment_status' => 'paid',
                'payment_method' => 'card',
                'invoice_date' => $today->toDateString(),
                'due_date' => $today->copy()->addDays(7)->toDateString(),
                'notes' => 'Corporate health discount of $10 applied.',
            ]
        );

        InvoiceItem::firstOrCreate(
            ['invoice_id' => $inv->id, 'description' => 'Cardiology Specialist Consultation - Dr. Marcus Vance'],
            [
                'item_type' => 'consultation',
                'quantity' => 1,
                'unit_price' => 75.00,
                'total_price' => 75.00,
            ]
        );

        InvoiceItem::firstOrCreate(
            ['invoice_id' => $inv->id, 'description' => 'Laboratory: Comprehensive Lipid Profile Panel'],
            [
                'item_type' => 'lab_test',
                'quantity' => 1,
                'unit_price' => 35.00,
                'total_price' => 35.00,
            ]
        );

        InvoiceItem::firstOrCreate(
            ['invoice_id' => $inv->id, 'description' => 'Medication: Amlodipine 5mg (30 Tablets)'],
            [
                'item_type' => 'medicine',
                'quantity' => 30,
                'unit_price' => 0.35,
                'total_price' => 10.50,
            ]
        );

        // Audit Logs Seed
        AuditLog::log('system.seed', 'Initialized database with core clinic data, specialists, catalog, and appointments.', null, $admin);
    }
}
