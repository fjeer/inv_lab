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
        Schema::table('procurement_items', function (Blueprint $table) {
            $table->foreignId('replaces_equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_items', function (Blueprint $table) {
            $table->dropForeign(['replaces_equipment_id']);
            $table->dropColumn('replaces_equipment_id');
        });
    }
};
