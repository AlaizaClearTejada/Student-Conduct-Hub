<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->string('recommended_violation')->nullable();
            $table->string('recommended_sanction')->nullable();
            $table->string('severity')->nullable();
            $table->integer('decision_score')->default(0);
            $table->json('matched_keywords')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropColumn([
                'recommended_violation',
                'recommended_sanction',
                'severity',
                'decision_score',
                'matched_keywords',
            ]);
        });
    }
};
