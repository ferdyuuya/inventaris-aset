<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Changes PIC from user_id to employee_id.
     * PIC (Person In Charge) should reference employees, not users,
     * because maintenance work is performed by employees.
     */
    public function up(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            // Drop old foreign key and column (pic_id references users)
            if (Schema::hasColumn('asset_maintenances', 'pic_id')) {
                $table->dropForeign(['pic_id']);
                $table->dropColumn('pic_id');
            }

            // Add new column referencing employees
            $table->unsignedBigInteger('pic_employee_id')->nullable()->after('created_by');

            $table->foreign('pic_employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            // Drop employee foreign key
            $table->dropForeign(['pic_employee_id']);
            $table->dropColumn('pic_employee_id');

            // Restore user foreign key
            $table->unsignedBigInteger('pic_id')->nullable()->after('created_by');

            $table->foreign('pic_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
