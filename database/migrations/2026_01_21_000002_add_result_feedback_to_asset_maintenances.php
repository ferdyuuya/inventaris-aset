<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds result and feedback columns to asset_maintenances.
     * - result: enum('baik', 'rusak') - technical outcome of maintenance
     * - feedback: text - technical explanation of work done
     */
    public function up(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->enum('result', ['baik', 'rusak'])->nullable()->after('description');
            $table->text('feedback')->nullable()->after('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->dropColumn(['result', 'feedback']);
        });
    }
};
