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
            $table->unsignedTinyInteger('notice_count')->default(0)->after('notice_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violation_records', function (Blueprint $table) {
            $table->dropColumn('notice_count');
        });
    }
};
