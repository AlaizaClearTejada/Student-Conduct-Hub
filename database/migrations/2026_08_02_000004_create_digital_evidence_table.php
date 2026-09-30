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
        Schema::create('digital_evidence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('offense_id')->constrained('offenses')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_type');
            $table->string('s3_key'); // or local path
            $table->integer('file_size');
            $table->enum('virus_scan_status', ['PENDING', 'CLEAN', 'FLAGGED'])->default('PENDING');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_evidence');
    }
};
