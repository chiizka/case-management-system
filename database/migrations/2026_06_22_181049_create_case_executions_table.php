<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('case_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_id');
            $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
            $table->string('received_by');
            $table->datetime('date_received');
            $table->string('tracking_no');
            $table->string('courier');
            $table->unsignedBigInteger('forwarded_by');
            $table->foreign('forwarded_by')->references('id')->on('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('case_executions');
    }
};
