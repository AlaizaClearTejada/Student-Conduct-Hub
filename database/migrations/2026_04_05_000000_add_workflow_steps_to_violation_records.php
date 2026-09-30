<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE violation_records MODIFY COLUMN status ENUM('Pending Review','Notice Sent','Under Investigation','Sanction Active','Resolved','Appealed') NOT NULL DEFAULT 'Pending Review'");
        }

        Schema::table('violation_records', function (Blueprint $table) {
            $table->datetime('notice_sent_at')->nullable()->after('action_taken');
            $table->foreignId('notice_sent_by')->nullable()->after('notice_sent_at')->constrained('users')->nullOnDelete();
            $table->datetime('conference_date')->nullable()->after('notice_sent_by');
            $table->text('conference_notes')->nullable()->after('conference_date');
            $table->datetime('conference_held_at')->nullable()->after('conference_notes');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE violation_records SET status = 'Pending Review' WHERE status IN ('Notice Sent', 'Under Investigation')");
            DB::statement("ALTER TABLE violation_records MODIFY COLUMN status ENUM('Pending Review','Sanction Active','Resolved','Appealed') NOT NULL DEFAULT 'Pending Review'");
        }

        Schema::table('violation_records', function (Blueprint $table) {
            $table->dropForeign(['notice_sent_by']);
            $table->dropColumn(['notice_sent_at', 'notice_sent_by', 'conference_date', 'conference_notes', 'conference_held_at']);
        });
    }
};
