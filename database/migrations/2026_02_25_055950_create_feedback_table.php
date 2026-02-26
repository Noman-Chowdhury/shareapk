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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['Bug', 'Suggestion', 'Other'])->default('Bug');
            $table->enum('severity', ['Low', 'Medium', 'High', 'Critical'])->nullable();
            $table->enum('status', ['Open', 'In Progress', 'Resolved', 'Closed'])->default('Open');
            
            // Device Context (Very important for APK testing)
            $table->string('device_model')->nullable();
            $table->string('os_version')->nullable();
            $table->string('screen_size')->nullable();
            
            $table->json('attachments')->nullable(); // Store screenshots/screen records
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
