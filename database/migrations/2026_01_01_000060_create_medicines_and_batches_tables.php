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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->index();
            $table->string('generic_name')->nullable()->index();
            $table->string('category')->index(); // Antibiotic, Analgesic, Antihistamine, Cardiovascular, Gastrointestinal, etc.
            $table->string('unit'); // Tablet, Capsule, Syrup, Injection, Cream, Drops
            $table->string('dosage_form')->nullable(); // e.g. 500mg, 100ml
            $table->string('manufacturer')->nullable();
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->decimal('selling_price', 10, 2)->default(0.00);
            $table->integer('reorder_level')->default(10);
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->onDelete('cascade');
            $table->string('batch_number')->index();
            $table->integer('quantity');
            $table->integer('remaining_quantity');
            $table->date('purchase_date')->nullable();
            $table->date('expiry_date')->index();
            $table->string('supplier')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('medicines');
    }
};
