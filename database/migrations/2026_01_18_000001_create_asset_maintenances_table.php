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
        if (!Schema::hasTable('asset_maintenances')) {
            Schema::create('asset_maintenances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('asset_id');
                $table->date('maintenance_date');
                $table->date('estimated_completion_date')->nullable();
                $table->date('completed_date')->nullable();
                $table->text('description');
                $table->enum('status', ['dalam_proses', 'selesai', 'dibatalkan'])->default('dalam_proses');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->foreign('asset_id')
                    ->references('id')
                    ->on('assets')
                    ->onDelete('cascade');

                $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
    }
};
