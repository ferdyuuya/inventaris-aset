<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds the pic_id (Person In Charge) column to asset_maintenances table.
     * This column tracks who is responsible for executing the maintenance work.
     */
    public function up(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->unsignedBigInteger('pic_id')->nullable()->after('created_by');

            $table->foreign('pic_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->dropForeign(['pic_id']);
            $table->dropColumn('pic_id');
        });
    }
};
