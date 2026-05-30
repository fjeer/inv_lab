<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Nama role (slug): admin, asisten, user');
            $table->string('display_name', 100)->comment('Label tampilan: Admin, Asisten Lab, Pengguna');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false)->comment('Role default untuk registrasi baru');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
