<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingLedgerTest extends TestCase
{
    use RefreshDatabase;

    private Patient $patient;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->patient = Patient::create([
            'patient_code' => 'PAT-2001',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'date_of_birth' => '1975-03-15',
            'gender' => 'male',
            'phone' => '5551234567',
        ]);
    }

    public function test_payments_ledger_records_transaction_and_updates_balance(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'patient_id' => $this->patient->id,
            'invoice_date' => now()->toDateString(),
            'payment_status' => 'unpaid',
            'subtotal' => 100.00,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => 100.00,
            'paid_amount' => 0.00,
            'balance_due' => 100.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'consultation',
            'description' => 'General Physician Consultation',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
        ]);

        // Record partial payment of $40
        $response1 = $this->actingAs($this->admin)->post("/billing/{$invoice->id}/payments", [
            'amount' => 40.00,
            'payment_method' => 'cash',
            'notes' => 'First partial installment',
        ]);

        $response1->assertRedirect();
        $invoice->refresh();

        $this->assertEquals(40.00, $invoice->paid_amount);
        $this->assertEquals(60.00, $invoice->balance_due);
        $this->assertEquals('partially_paid', $invoice->payment_status);
        $this->assertEquals(1, $invoice->payments()->count());

        $firstPayment = $invoice->payments()->first();
        $this->assertStringStartsWith('PAY-', $firstPayment->payment_reference);
        $this->assertEquals($this->admin->id, $firstPayment->recorded_by);

        // Record remaining payment of $60
        $response2 = $this->actingAs($this->admin)->post("/billing/{$invoice->id}/payments", [
            'amount' => 60.00,
            'payment_method' => 'card',
            'notes' => 'Final settlement',
        ]);

        $response2->assertRedirect();
        $invoice->refresh();

        $this->assertEquals(100.00, $invoice->paid_amount);
        $this->assertEquals(0.00, $invoice->balance_due);
        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertEquals(2, $invoice->payments()->count());
    }

    public function test_zero_dollar_invoice_automatically_marks_as_paid(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'patient_id' => $this->patient->id,
            'invoice_date' => now()->toDateString(),
            'payment_status' => 'unpaid',
            'subtotal' => 0.00,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => 0.00,
            'paid_amount' => 0.00,
            'balance_due' => 0.00,
        ]);

        $invoice->recalculateTotals();

        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertEquals(0.00, $invoice->balance_due);
    }

    public function test_lab_order_automatically_creates_invoice_if_none_exists(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'staff_id' => 'DOC-003',
            'license_number' => 'LIC-DOC-003',
            'specialization' => 'Pathology',
            'consultation_fee' => 40.00,
            'status' => 'active',
        ]);

        $labTest = LabTest::create([
            'code' => 'CBC-001',
            'name' => 'Complete Blood Count',
            'category' => 'Hematology',
            'price' => 35.00,
        ]);

        $this->assertEquals(0, Invoice::where('patient_id', $this->patient->id)->count());

        $response = $this->actingAs($this->admin)->post('/lab/orders', [
            'patient_id' => $this->patient->id,
            'doctor_id' => $doctor->id,
            'lab_test_id' => $labTest->id,
            'order_date' => now()->toDateString(),
            'clinical_notes' => 'Routine screening',
        ]);

        $response->assertRedirect();

        // An invoice should have been created with the lab test item
        $invoice = Invoice::where('patient_id', $this->patient->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(35.00, $invoice->total_amount);
        $this->assertEquals(1, $invoice->items()->count());
        $this->assertEquals('lab_test', $invoice->items()->first()->item_type);
    }
}
