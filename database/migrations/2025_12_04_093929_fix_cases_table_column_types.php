<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, convert existing enum Y/N data to 0/1
        DB::statement("UPDATE cases SET first_order_dismissal_cnpc = CASE WHEN first_order_dismissal_cnpc = 'Y' THEN '1' WHEN first_order_dismissal_cnpc = 'N' THEN '0' ELSE NULL END");
        DB::statement("UPDATE cases SET tavable_less_than_10_workers = CASE WHEN tavable_less_than_10_workers = 'Y' THEN '1' WHEN tavable_less_than_10_workers = 'N' THEN '0' ELSE NULL END");
        DB::statement("UPDATE cases SET with_deposited_monetary_claims = CASE WHEN with_deposited_monetary_claims = 'Y' THEN '1' WHEN with_deposited_monetary_claims = 'N' THEN '0' ELSE NULL END");
        DB::statement("UPDATE cases SET with_order_payment_notice = CASE WHEN with_order_payment_notice = 'Y' THEN '1' WHEN with_order_payment_notice = 'N' THEN '0' ELSE NULL END");
        DB::statement("UPDATE cases SET updated_ticked_in_mis = CASE WHEN updated_ticked_in_mis = 'Y' THEN '1' WHEN updated_ticked_in_mis = 'N' THEN '0' ELSE NULL END");
        
        Schema::table('cases', function (Blueprint $table) {
            // Fix Date/Text Mismatches - Change from DATE to STRING
            $table->string('lapse_20_day_period')->nullable()->change();
            $table->string('pct_for_docketing')->nullable()->change();
            $table->string('first_mc_pct')->nullable()->change();
            $table->string('second_last_mc_pct')->nullable()->change();
            $table->string('po_pct')->nullable()->change();
            $table->string('case_folder_forwarded_to_ro')->nullable()->change();
            
            // Fix Integer to String
            $table->string('pct_96_days')->nullable()->change();
            
            // Fix Boolean Fields - Change from ENUM to TINYINT (0/1)
            // Drop and recreate to avoid enum issues
            $table->dropColumn([
                'first_order_dismissal_cnpc',
                'tavable_less_than_10_workers',
                'with_deposited_monetary_claims',
                'with_order_payment_notice',
                'updated_ticked_in_mis'
            ]);
        });
        
        Schema::table('cases', function (Blueprint $table) {
            // Add back as boolean (tinyint)
            $table->boolean('first_order_dismissal_cnpc')->default(0)->nullable()->after('released_date_actual');
            $table->boolean('tavable_less_than_10_workers')->default(0)->nullable()->after('first_order_dismissal_cnpc');
            $table->boolean('with_deposited_monetary_claims')->default(0)->nullable()->after('scanned_order_first');
            $table->boolean('with_order_payment_notice')->default(0)->nullable()->after('amount_deposited');
            $table->boolean('updated_ticked_in_mis')->default(0)->nullable()->after('scanned_notice_finality');
        });
    }

    public function down()
    {
        Schema::table('cases', function (Blueprint $table) {
            // Revert back to original types
            $table->date('lapse_20_day_period')->nullable()->change();
            $table->date('pct_for_docketing')->nullable()->change();
            $table->date('first_mc_pct')->nullable()->change();
            $table->date('second_last_mc_pct')->nullable()->change();
            $table->date('po_pct')->nullable()->change();
            $table->date('case_folder_forwarded_to_ro')->nullable()->change();
            $table->integer('pct_96_days')->nullable()->change();
            
            // Revert booleans back to enum
            $table->dropColumn([
                'first_order_dismissal_cnpc',
                'tavable_less_than_10_workers',
                'with_deposited_monetary_claims',
                'with_order_payment_notice',
                'updated_ticked_in_mis'
            ]);
        });
        
        Schema::table('cases', function (Blueprint $table) {
            $table->enum('first_order_dismissal_cnpc', ['Y', 'N'])->nullable();
            $table->enum('tavable_less_than_10_workers', ['Y', 'N'])->nullable();
            $table->enum('with_deposited_monetary_claims', ['Y', 'N'])->nullable();
            $table->enum('with_order_payment_notice', ['Y', 'N'])->nullable();
            $table->enum('updated_ticked_in_mis', ['Y', 'N'])->nullable();
        });
    }
};