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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('consultation_number')->unique();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->dateTime('consultation_date')->index();
            $table->text('chief_complaint');
            $table->text('history_of_present_illness')->nullable();
            $table->text('physical_examination')->nullable();
            $table->string('primary_diagnosis')->index();
            $table->string('icd10_code')->nullable()->index();
            $table->text('secondary_diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('doctor_notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('status')->default('completed')->index(); // in_progress, completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
