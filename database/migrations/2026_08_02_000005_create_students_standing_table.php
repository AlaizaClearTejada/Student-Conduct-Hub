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
        Schema::create('students_standing', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('student_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->enum('standing_status', ['GOOD', 'REVIEW', 'PROBATION', 'SUSPENDED', 'EXPELLED'])->default('GOOD');
            $table->integer('active_cases_count')->default(0);
            $table->integer('major_offenses_count')->default(0);
            $table->json('pending_sanctions')->nullable(); // JSON structure for sanctions
            $table->timestamp('standing_computed_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students_standing');
    }
};
