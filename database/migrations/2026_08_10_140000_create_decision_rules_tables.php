<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decision_rules', function (Blueprint $table) {
            $table->id();
            $table->string('violation_name');
            $table->text('description')->nullable();
            $table->string('severity');
            $table->string('recommended_action');
            $table->integer('threshold')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('decision_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_rule_id')->constrained()->cascadeOnDelete();
            $table->string('keyword');
            $table->integer('weight')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decision_keywords');
        Schema::dropIfExists('decision_rules');
    }
};
