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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100);
            $table->foreignId('case_id')->constrained('tribunal_cases')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->enum('recipient_type', ['student', 'staff', 'tribunal', 'complainant']);
            $table->text('subject');
            $table->text('body');
            $table->json('channels');
            $table->string('template_key', 100)->nullable();
            $table->json('template_data')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('is_encrypted')->default(false);
            $table->binary('encrypted_body')->nullable();
            $table->enum('status', ['pending', 'queued', 'sent', 'delivered', 'failed', 'suppressed'])->default('pending');

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('failed_reason', 500)->nullable();
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);

            $table->timestamps();

            $table->index('event_type');
            $table->index(['recipient_id', 'status']);
            $table->index('created_at');
        });

        Schema::create('notification_channel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->enum('channel', ['sms', 'email', 'in_app', 'push']);
            $table->string('external_id')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'bounced'])->default('pending');
            $table->string('recipient_contact');
            $table->json('provider_response')->nullable();
            $table->string('error_message', 500)->nullable();
            $table->decimal('cost', 10, 4)->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['notification_id', 'channel']);
            $table->index('external_id');
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->boolean('sms_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);

            $table->integer('max_sms_per_day')->default(5);
            $table->integer('max_emails_per_day')->default(10);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();

            $table->json('opted_out_events')->nullable();

            $table->string('phone_number', 20)->nullable();
            $table->boolean('verified_phone')->default(false);
            $table->string('secondary_email')->nullable();

            $table->timestamps();
        });

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->text('sms_template')->nullable();
            $table->string('email_subject')->nullable();
            $table->longText('email_template')->nullable();
            $table->text('in_app_template')->nullable();

            $table->json('variables')->nullable();

            $table->string('event_type', 100);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->json('channels')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['key', 'event_type'], 'unique_key_event');
            $table->index('event_type');
        });

        Schema::create('notification_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('event_type', 100);
            $table->json('condition_json')->nullable();

            $table->json('recipient_roles')->nullable();
            $table->json('channels')->nullable();

            $table->integer('priority')->default(100);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('event_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_rules');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_channel_logs');
        Schema::dropIfExists('notifications');
    }
};
