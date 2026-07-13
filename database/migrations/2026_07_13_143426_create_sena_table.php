<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sena', function (Blueprint $table) {
            $table->id();

            // Not connected to any table yet — plain column, no FK constraint
            $table->unsignedBigInteger('case_id')->nullable();

            $table->string('regional_docket_number', 100)->nullable();
            $table->string('sheriff_designate')->nullable();
            $table->date('date_compliance_order')->nullable();
            $table->text('voluntary_compliance')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('full_or_partial')->nullable();

            $table->decimal('total_gls_monetary_award', 15, 2)->nullable();
            $table->integer('total_workers_benefited')->nullable();
            $table->decimal('amount_penalty_double_indemnity', 15, 2)->nullable();
            $table->decimal('total_gls_monetary_satisfied', 15, 2)->nullable();
            $table->integer('total_workers_satisfied')->nullable();
            $table->integer('total_workers_absorbed')->nullable();
            $table->text('complied_oshs_violations')->nullable();
            $table->decimal('total_penalty_double_indemnity_collected', 15, 2)->nullable();
            $table->decimal('total_oshs_penalty_admin_fines_collected', 15, 2)->nullable();

            $table->date('date_writ_of_execution_served')->nullable();
            $table->date('date_indorsed_to_po')->nullable();
            $table->date('po_date_received')->nullable();
            $table->date('ro_received_sheriffs_return')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sena');
    }
};