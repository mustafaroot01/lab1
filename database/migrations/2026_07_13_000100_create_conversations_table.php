<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('open'); // ConversationStatus enum
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->foreignId('last_sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('last_message_preview')->nullable();
            $table->unsignedBigInteger('admin_last_read_message_id')->nullable();
            $table->unsignedBigInteger('patient_last_read_message_id')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
