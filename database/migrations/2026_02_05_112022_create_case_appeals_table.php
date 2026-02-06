<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_appeals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_id')->unique();
            $table->enum('appellate_body', [
                'Office of the Secretary',
                'NLRC',
                'BLR'
            ]);
            $table->date('transmittal_date');
            $table->string('destination')->default('Central Office - Manila');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('case_id')
                  ->references('id')
                  ->on('cases')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_appeals');
    }
};