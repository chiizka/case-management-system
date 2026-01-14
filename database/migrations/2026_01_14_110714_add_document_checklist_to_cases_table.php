<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->json('document_checklist')->nullable()->after('remarks_notes');
        });
    }

    public function down()
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('document_checklist');
        });
    }
};