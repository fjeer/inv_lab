<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_id')->constrained('laboratories')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Dosen pengampu');
            $table->string('title')->comment('Nama mata kuliah / kegiatan');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('semester', 20)->nullable()->comment('e.g. Gasal 2025/2026');
            $table->string('academic_year', 20)->nullable();
            $table->string('class_group', 50)->nullable()->comment('Kelas / Kelompok');
            $table->enum('status', ['active', 'inactive', 'cancelled'])->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_schedules');
    }
};
