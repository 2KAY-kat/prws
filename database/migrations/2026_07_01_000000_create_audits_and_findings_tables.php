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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->unsignedInteger('score')->nullable();
            $table->string('certification')->nullable();
            $table->timestamps();
        });

        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained()->cascadeOnDelete();
            $table->string('rule_id');
            $table->string('name');
            $table->string('category');
            $table->string('severity');
            $table->unsignedInteger('points_available');
            $table->unsignedInteger('points_earned');
            $table->boolean('passed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('findings');
        Schema::dropIfExists('audits');
    }
};
