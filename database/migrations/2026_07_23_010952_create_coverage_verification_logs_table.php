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
        Schema::create('coverage_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->foreignId('matched_zone_id')->nullable()->constrained('coverage_zones')->nullOnDelete();
            $table->float('execution_time_ms')->nullable();
            $table->string('algorithm_used')->nullable();
            $table->boolean('inside_polygon')->default(false);
            $table->boolean('grace_match')->default(false);
            $table->float('distance_from_border')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coverage_verification_logs');
    }
};
