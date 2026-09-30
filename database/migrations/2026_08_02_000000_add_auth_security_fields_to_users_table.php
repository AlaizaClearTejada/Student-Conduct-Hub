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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('password');
            }
            if (! Schema::hasColumn('users', 'mfa_enabled')) {
                $table->boolean('mfa_enabled')->default(false)->after('must_change_password');
            }
            if (! Schema::hasColumn('users', 'mfa_otp')) {
                $table->string('mfa_otp', 10)->nullable()->after('mfa_enabled');
            }
            if (! Schema::hasColumn('users', 'mfa_otp_expires_at')) {
                $table->timestamp('mfa_otp_expires_at')->nullable()->after('mfa_otp');
            }
            if (! Schema::hasColumn('users', 'mfa_otp_attempts')) {
                $table->unsignedTinyInteger('mfa_otp_attempts')->default(0)->after('mfa_otp_expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'must_change_password',
                'mfa_enabled',
                'mfa_otp',
                'mfa_otp_expires_at',
                'mfa_otp_attempts',
            ]);
        });
    }
};
