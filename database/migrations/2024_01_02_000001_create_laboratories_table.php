<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('location')->nullable()->comment('Building / Floor / Room');
            $table->unsignedInteger('capacity')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('responsible_person_id')->nullable()->constrained('users')->nullOnDelete()->comment('PJ Lab / Kepala Lab');
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratories');
    }
};
