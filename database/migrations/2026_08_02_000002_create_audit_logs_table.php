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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('entity_type'); // e.g., 'users', 'offenses', 'standing'
            $table->string('entity_id'); // Using string to support both UUID and BigInt IDs
            $table->string('action'); // CREATE, UPDATE, DELETE, RESTORE, SUSPEND
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('changes')->nullable(); // {field: old_value, new_value} - SQLite doesn't natively support jsonb in schema builder
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
