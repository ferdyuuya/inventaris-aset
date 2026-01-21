<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 1. Adds 'dihapuskan' status to assets table
     * 2. Adds disposed_at column to assets table
     * 3. Creates asset_disposals audit table
     */
    public function up(): void
    {
        // Step 1: Add 'dihapuskan' to assets status ENUM
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('aktif', 'dipinjam', 'dipelihara', 'nonaktif', 'dihapuskan') DEFAULT 'aktif'");

        // Step 2: Add disposed_at column to assets
        Schema::table('assets', function (Blueprint $table) {
            $table->timestamp('disposed_at')->nullable()->after('status');
        });

        // Step 3: Create asset_disposals audit table
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('disposed_by');
            $table->text('reason');
            $table->timestamp('disposed_at');
            $table->timestamps();

            $table->foreign('asset_id')
                ->references('id')
                ->on('assets')
                ->onDelete('cascade');

            $table->foreign('disposed_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop asset_disposals table
        Schema::dropIfExists('asset_disposals');

        // Remove disposed_at column from assets
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('disposed_at');
        });

        // Revert status ENUM (remove 'dihapuskan')
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('aktif', 'dipinjam', 'dipelihara', 'nonaktif') DEFAULT 'aktif'");
    }
};
