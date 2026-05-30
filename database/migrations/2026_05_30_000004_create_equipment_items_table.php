<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->unsignedInteger('sequence_number')->comment('Nomer urut item dalam equipment');
            $table->string('qr_code', 255)->unique()->comment('Format: kode_gedung kode_ruangan kode_lab nama_alat nomer_urut');
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])->default('baik')->index();
            $table->text('condition_notes')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->foreignId('last_checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['equipment_id', 'sequence_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_items');
    }
};
