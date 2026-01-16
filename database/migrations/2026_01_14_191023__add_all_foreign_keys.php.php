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
        Schema::enableForeignKeyConstraints();
        
        Schema::table('employees', fn($t)=>
            $t->foreign('user_id')->references('id')->on('users')->nullOnDelete()
        );

        Schema::table('locations', fn($t)=>
            $t->foreign('responsible_employee_id')->references('id')->on('employees')->nullOnDelete()
        );

        Schema::table('procurements', fn($t)=>[
            $t->foreign('asset_category_id')->references('id')->on('asset_categories')->restrictOnDelete(),
            $t->foreign('location_id')->references('id')->on('locations')->restrictOnDelete(),
            $t->foreign('supplier_id')->references('id')->on('suppliers')->restrictOnDelete(),
            $t->foreign('created_by')->references('id')->on('users')->restrictOnDelete(),
        ]);

        Schema::table('assets', fn($t)=>[
            $t->foreign('category_id')->references('id')->on('asset_categories')->restrictOnDelete(),
            $t->foreign('location_id')->references('id')->on('locations')->restrictOnDelete(),
        ]);

        Schema::table('asset_transactions', fn($t)=>[
            $t->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete(),
            $t->foreign('from_location_id')->references('id')->on('locations')->nullOnDelete(),
            $t->foreign('to_location_id')->references('id')->on('locations')->nullOnDelete(),
            $t->foreign('created_by')->references('id')->on('users')->restrictOnDelete(),
        ]);

        Schema::table('asset_loans', fn($t)=>[
            $t->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete(),
            $t->foreign('borrower_employee_id')->references('id')->on('employees')->restrictOnDelete(),
        ]);

        Schema::table('maintenance_requests', fn($t)=>[
            $t->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete(),
            $t->foreign('requested_by')->references('id')->on('users')->restrictOnDelete(),
            $t->foreign('approved_by')->references('id')->on('users')->nullOnDelete(),
        ]);

        Schema::table('asset_maintenances', fn($t)=>
            $t->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete()
        );

        // Schema::table('reports', fn($t)=>
        //     $t->foreign('generated_by')->references('id')->on('users')->restrictOnDelete()
        // );

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        
        Schema::table('employees', fn($t)=>
            $t->dropForeign(['user_id'])
        );

        Schema::table('locations', fn($t)=>
            $t->dropForeign(['responsible_employee_id'])
        );

        Schema::table('procurements', fn($t)=>[
            $t->dropForeign(['supplier_id']),
            $t->dropForeign(['asset_category_id']),
            $t->dropForeign(['created_by']),
        ]);

        Schema::table('assets', fn($t)=>[
            $t->dropForeign(['category_id']),
            $t->dropForeign(['location_id']),
        ]);

        Schema::table('asset_transactions', fn($t)=>[
            $t->dropForeign(['asset_id']),
            $t->dropForeign(['from_location_id']),
            $t->dropForeign(['to_location_id']),
            $t->dropForeign(['created_by']),
        ]);

        Schema::table('asset_loans', fn($t)=>[
            $t->dropForeign(['asset_id']),
            $t->dropForeign(['borrower_employee_id']),
        ]);

        Schema::table('maintenance_requests', fn($t)=>[
            $t->dropForeign(['asset_id']),
            $t->dropForeign(['requested_by']),
            $t->dropForeign(['approved_by']),
        ]);

        Schema::table('asset_maintenances', fn($t)=>
            $t->dropForeign(['asset_id'])
        );

        // Schema::table('reports', fn($t)=>
        //     $t->dropForeign(['generated_by'])
        // );
        
        Schema::enableForeignKeyConstraints();
    }
};
