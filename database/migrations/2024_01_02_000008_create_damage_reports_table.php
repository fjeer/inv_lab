<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete()->comment('Admin/Asisten');
            $table->enum('damage_type', ['ringan', 'sedang', 'berat'])->default('ringan');
            $table->text('description')->comment('Deskripsi kerusakan');
            $table->date('incident_date');
            $table->string('photo')->nullable();
            $table->enum('status', ['reported', 'in_review', 'in_repair', 'repaired', 'unrepairable', 'closed'])->default('reported')->index();
            $table->decimal('repair_cost', 15, 2)->default(0);
            $table->text('repair_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
