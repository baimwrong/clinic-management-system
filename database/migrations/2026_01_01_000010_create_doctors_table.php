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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('staff_id')->unique();
            $table->string('specialization')->index();
            $table->string('license_number')->unique();
            $table->string('qualification')->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(50.00);
            $table->string('room_number')->nullable();
            $table->text('bio')->nullable();
            $table->json('available_days')->nullable();
            $table->time('available_time_start')->default('08:00:00');
            $table->time('available_time_end')->default('17:00:00');
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
