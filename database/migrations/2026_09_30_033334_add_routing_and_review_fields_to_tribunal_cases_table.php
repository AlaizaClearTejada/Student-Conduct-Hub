<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tribunal_cases', function (Blueprint $table) {
            $table->foreignId('incident_report_id')->nullable()->unique()->constrained('incident_reports')->nullOnDelete();
            $table->foreignId('violation_record_id')->nullable()->unique()->constrained('violation_records')->nullOnDelete();
            $table->string('document_path')->nullable();
            $table->string('document_disk')->default('local');
            $table->longText('searchable_text')->nullable();
        });

        DB::table('tribunal_cases')
            ->whereIn('status', ['active', 'pending'])
            ->update(['status' => 'under_formal_investigation']);

        DB::table('tribunal_cases')
            ->where('status', 'closed')
            ->update(['status' => 'case_resolved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tribunal_cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('incident_report_id');
            $table->dropConstrainedForeignId('violation_record_id');
            $table->dropColumn(['document_path', 'document_disk', 'searchable_text']);
        });
    }
};
