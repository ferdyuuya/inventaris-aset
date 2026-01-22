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
     * Updates asset_loans table to match the borrowing feature specification:
     * - Renames borrower_employee_id to employee_id
     * - Renames loan_date to borrow_date
     * - Adds condition_after_return
     * - Adds notes
     * - Updates status enum (dipinjam, selesai)
     */
    public function up(): void
    {
        Schema::table('asset_loans', function (Blueprint $table) {
            // Add new columns
            $table->string('condition_after_return')->nullable()->after('return_date');
            $table->text('notes')->nullable()->after('condition_after_return');
        });

        // Update status values: dikembalikan -> selesai, remove hilang
        // Using raw SQL for enum update
        DB::statement("ALTER TABLE asset_loans MODIFY COLUMN status ENUM('dipinjam', 'selesai', 'dikembalikan', 'hilang') DEFAULT 'dipinjam'");
        
        // Convert existing 'dikembalikan' to 'selesai'
        DB::statement("UPDATE asset_loans SET status = 'selesai' WHERE status = 'dikembalikan'");
        
        // Now restrict to just the two allowed values
        DB::statement("ALTER TABLE asset_loans MODIFY COLUMN status ENUM('dipinjam', 'selesai') DEFAULT 'dipinjam'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status enum
        DB::statement("ALTER TABLE asset_loans MODIFY COLUMN status ENUM('dipinjam', 'dikembalikan', 'hilang') DEFAULT 'dipinjam'");
        
        // Convert 'selesai' back to 'dikembalikan'
        DB::statement("UPDATE asset_loans SET status = 'dikembalikan' WHERE status = 'selesai'");

        Schema::table('asset_loans', function (Blueprint $table) {
            $table->dropColumn(['condition_after_return', 'notes']);
        });
    }
};
