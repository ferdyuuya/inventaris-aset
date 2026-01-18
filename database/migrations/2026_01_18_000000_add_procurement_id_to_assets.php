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
        Schema::table('assets', function (Blueprint $table) {
            // Add procurement_id if it doesn't exist
            if (!Schema::hasColumn('assets', 'procurement_id')) {
                $table->unsignedBigInteger('procurement_id')->nullable()->after('id');
                $table->foreign('procurement_id')
                    ->references('id')
                    ->on('procurements')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'procurement_id')) {
                $table->dropForeign(['procurement_id']);
                $table->dropColumn('procurement_id');
            }
        });
    }
};
