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
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable'); // Allows linking to Patient, Technician, etc.
            $table->string('title');
            $table->text('body');
            $table->string('type')->nullable(); // e.g. order_status, promotion
            $table->string('status')->default('sent'); // sent, failed
            $table->json('payload')->nullable(); // Additional data
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};

