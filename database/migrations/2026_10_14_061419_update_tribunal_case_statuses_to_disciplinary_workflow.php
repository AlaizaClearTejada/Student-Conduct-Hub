<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tribunal_cases')
            ->whereIn('status', ['active', 'pending', 'under_informal_discussion', 'under_formal_investigation'])
            ->update(['status' => 'under_investigation']);

        DB::table('tribunal_cases')
            ->where('status', 'case_resolved')
            ->update(['status' => 'closed']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tribunal_cases')
            ->whereIn('status', ['under_investigation', 'notice_issued', 'hearing_scheduled'])
            ->update(['status' => 'under_formal_investigation']);

        DB::table('tribunal_cases')
            ->whereIn('status', ['closed', 'appealed'])
            ->update(['status' => 'case_resolved']);
    }
};
