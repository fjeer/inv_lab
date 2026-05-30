<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrol_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrol_schedule_id')->constrained('patrol_schedules')->cascadeOnDelete();
            $table->foreignId('equipment_item_id')->constrained('equipment_items')->cascadeOnDelete();
            $table->foreignId('checked_by')->constrained('users')->cascadeOnDelete();
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang']);
            $table->enum('previous_condition', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])->nullable();
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->timestamps();

            $table->index(['patrol_schedule_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrol_logs');
    }
};
