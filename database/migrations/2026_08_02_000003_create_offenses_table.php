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
        Schema::create('offenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('filed_by')->constrained('users')->cascadeOnDelete();
            $table->string('case_number')->unique();
            $table->enum('offense_type', ['MINOR', 'MAJOR', 'CRITICAL']);
            $table->string('offense_category'); // e.g., 'ACADEMIC_DISHONESTY', 'CONDUCT_VIOLATION', etc.
            $table->date('incident_date');
            $table->time('incident_time');
            $table->string('location');
            $table->text('description');
            $table->json('witnesses')->nullable(); // array of names or IDs
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'UNDER_INVESTIGATION', 'RESOLVED', 'APPEALED'])->default('DRAFT');
            $table->foreignId('tribunal_assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('tribunal_decision')->nullable();
            $table->timestamp('resolution_date')->nullable();
            $table->json('sanction_details')->nullable(); // community_service_hours, fine, restrictions
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offenses');
    }
};
