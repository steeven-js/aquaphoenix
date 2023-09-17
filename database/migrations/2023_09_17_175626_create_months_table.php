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
        Schema::create('months', function (Blueprint $table) {
            $table->id();
            $table->string('year')->nullable();
            $table->string('month')->nullable();
            $table->string('month_number')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();

            $table->integer('count')->default(0)->nullable();
            $table->boolean('report_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('months');
    }
};
