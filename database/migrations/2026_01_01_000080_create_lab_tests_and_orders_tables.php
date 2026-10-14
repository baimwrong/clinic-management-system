<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->index();
            $table->string('category')->index(); // Hematology, Biochemistry, Microbiology, Radiology, Pathology
            $table->decimal('price', 10, 2)->default(0.00);
            $table->text('reference_range')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('consultation_id')->nullable()->constrained('consultations')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade');
            $table->dateTime('order_date')->index();
            $table->string('status')->default('ordered')->index(); // ordered, in_progress, completed, cancelled
            $table->text('result_value')->nullable();
            $table->string('result_flag')->nullable(); // normal, abnormal, critical
            $table->text('technician_notes')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('lab_tests');
    }
};
