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
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->foreignId('equipment_item_id')->nullable()->after('equipment_id')->constrained('equipment_items')->nullOnDelete();
        });

        Schema::table('equipment_conditions', function (Blueprint $table) {
            $table->foreignId('equipment_item_id')->nullable()->after('equipment_id')->constrained('equipment_items')->nullOnDelete();
        });

        Schema::table('procurement_items', function (Blueprint $table) {
            $table->foreignId('replaces_equipment_item_id')->nullable()->after('replaces_equipment_id')->constrained('equipment_items')->nullOnDelete();
        });

        Schema::table('equipment_items', function (Blueprint $table) {
            $table->foreignId('replaces_equipment_item_id')->nullable()->after('last_checked_by')->constrained('equipment_items')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_items', function (Blueprint $table) {
            $table->dropForeign(['replaces_equipment_item_id']);
            $table->dropColumn('replaces_equipment_item_id');
        });

        Schema::table('procurement_items', function (Blueprint $table) {
            $table->dropForeign(['replaces_equipment_item_id']);
            $table->dropColumn('replaces_equipment_item_id');
        });

        Schema::table('equipment_conditions', function (Blueprint $table) {
            $table->dropForeign(['equipment_item_id']);
            $table->dropColumn('equipment_item_id');
        });

        Schema::table('damage_reports', function (Blueprint $table) {
            $table->dropForeign(['equipment_item_id']);
            $table->dropColumn('equipment_item_id');
        });
    }
};
