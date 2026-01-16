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
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('asset_category_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('supplier_id');
            $table->date('procurement_date');
            $table->string('invoice_number')->nullable();
            $table->decimal('total_cost',15,0);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};
