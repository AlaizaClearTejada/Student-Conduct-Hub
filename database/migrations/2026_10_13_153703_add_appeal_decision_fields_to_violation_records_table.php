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
        Schema::table('violation_records', function (Blueprint $table) {
            $table->string('appeal_decision')->nullable()->after('appeal_deadline');
            $table->text('appeal_decision_notes')->nullable()->after('appeal_decision');
            $table->foreignId('appeal_decided_by')->nullable()->after('appeal_decision_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('appeal_decided_at')->nullable()->after('appeal_decided_by');
            $table->integer('offense_count_adjustment')->default(0)->after('appeal_decided_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violation_records', function (Blueprint $table) {
            $table->dropForeign(['appeal_decided_by']);
            $table->dropColumn([
                'appeal_decision',
                'appeal_decision_notes',
                'appeal_decided_by',
                'appeal_decided_at',
                'offense_count_adjustment',
            ]);
        });
    }
};
