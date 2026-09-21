<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cases MODIFY first_order_dismissal_cnpc TINYINT(1) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE cases MODIFY tavable_less_than_10_workers TINYINT(1) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE cases MODIFY with_deposited_monetary_claims TINYINT(1) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE cases MODIFY with_order_payment_notice TINYINT(1) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE cases MODIFY updated_ticked_in_mis TINYINT(1) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cases MODIFY first_order_dismissal_cnpc TINYINT(1) NULL DEFAULT 0");
        DB::statement("ALTER TABLE cases MODIFY tavable_less_than_10_workers TINYINT(1) NULL DEFAULT 0");
        DB::statement("ALTER TABLE cases MODIFY with_deposited_monetary_claims TINYINT(1) NULL DEFAULT 0");
        DB::statement("ALTER TABLE cases MODIFY with_order_payment_notice TINYINT(1) NULL DEFAULT 0");
        DB::statement("ALTER TABLE cases MODIFY updated_ticked_in_mis TINYINT(1) NULL DEFAULT 0");
    }
};
