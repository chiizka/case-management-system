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
        Schema::create('compliance_and_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->decimal('compliance_order_monetary_award', 15, 2)->nullable();
            $table->decimal('osh_penalty', 15, 2)->nullable();
            $table->integer('affected_male')->nullable();
            $table->integer('affected_female')->nullable();
            $table->boolean('first_order_dismissal_cnpc')->default(false);
            $table->boolean('tavable_less_than_10_workers')->default(false);
            $table->boolean('with_deposited_monetary_claims')->default(false);
            $table->decimal('amount_deposited', 15, 2)->nullable();
            $table->boolean('with_order_payment_notice')->default(false);
            $table->string('status_all_employees_received')->nullable();
            $table->string('status_case_after_first_order')->nullable();
            $table->date('date_notice_finality_dismissed')->nullable();
            $table->date('released_date_notice_finality')->nullable();
            $table->boolean('updated_ticked_in_mis')->default(false);
            $table->string('second_order_drafter')->nullable();
            $table->date('date_received_by_drafter_ct_cnpc')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_and_awards');
    }
};
