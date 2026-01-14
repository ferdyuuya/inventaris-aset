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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('location_id');
            $table->date('purchase_date');
            $table->decimal('purchase_price',15,0);
            $table->enum('condition',['baik','rusak','perlu_perbaikan'])->default('baik');
            $table->boolean('is_available')->default(true);
            $table->enum('status',['aktif','dipinjam','nonaktif'])->default('aktif');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
