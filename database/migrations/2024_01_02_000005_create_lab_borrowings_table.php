<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_id')->constrained('laboratories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('Peminjam');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->comment('Admin/Asisten');
            $table->text('purpose')->comment('Tujuan peminjaman');
            $table->string('activity_type', 100)->nullable()->comment('Jenis kegiatan');
            $table->date('borrow_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'approved', 'rejected', 'ongoing', 'completed', 'cancelled'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_borrowings');
    }
};
