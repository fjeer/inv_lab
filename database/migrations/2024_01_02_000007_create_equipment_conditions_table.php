<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('checked_by')->constrained('users')->cascadeOnDelete()->comment('User yang memeriksa');
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang']);
            $table->enum('previous_condition', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])->nullable();
            $table->date('check_date')->index();
            $table->text('description')->nullable()->comment('Detail kondisi');
            $table->text('action_taken')->nullable()->comment('Tindakan yang diambil');
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_conditions');
    }
};
