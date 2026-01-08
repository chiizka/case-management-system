<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->text('establishment_address')->nullable()->after('establishment_name');
            $table->string('mode')->nullable()->after('establishment_address');
        });
    }

    public function down()
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['establishment_address', 'mode']);
        });
    }
};