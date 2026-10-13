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
        Schema::table('tribunal_cases', function (Blueprint $table) {
            $table->string('respondent_name')->nullable()->after('title');
            $table->date('incident_date')->nullable()->after('respondent_name');
            $table->string('category')->nullable()->after('incident_date');
            $table->string('priority')->default('normal')->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tribunal_cases', function (Blueprint $table) {
            $table->dropColumn(['respondent_name', 'incident_date', 'category', 'priority']);
        });
    }
};
