<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coverage_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            
            $table->enum('coverage_type', ['POLYGON', 'RADIUS'])->default('POLYGON');
            
            // GEOMETRY column to hold either Polygon or Point(for radius)
            $table->geometry('geometry'); 
            
            // For RADIUS type, we store the radius in meters
            $table->integer('radius_meters')->nullable();
            
            $table->enum('pricing_type', ['FIXED', 'RULE_BASED'])->default('FIXED');
            $table->decimal('service_fee', 10, 2)->default(0);
            
            $table->integer('priority')->default(0);
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'MAINTENANCE'])->default('ACTIVE');
            
            $table->dateTime('effective_from')->nullable();
            $table->dateTime('effective_to')->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Add SPATIAL INDEX only if using MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE coverage_zones ADD SPATIAL INDEX coverage_zones_geometry_spatial_index(geometry)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coverage_zones');
    }
};
