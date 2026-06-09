<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaseTagToDocumentTracking extends Migration
{
    public function up()
    {
        Schema::table('document_tracking', function (Blueprint $table) {
            $table->string('case_tag')->nullable()->after('transfer_notes');
        });
    }

    public function down()
    {
        Schema::table('document_tracking', function (Blueprint $table) {
            $table->dropColumn('case_tag');
        });
    }
}