<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the status column to include 'dipelihara' (maintenance)
        // Using raw SQL because ENUM changes are not well supported in Laravel
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('aktif', 'dipinjam', 'dipelihara', 'nonaktif') DEFAULT 'aktif'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('aktif', 'dipinjam', 'nonaktif') DEFAULT 'aktif'");
    }
};
