<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('months', function (Blueprint $table) {
            $table->id();
            $table->string('year')->nullable();
            $table->string('month')->nullable();
            $table->string('month_number')->nullable();
            $table->integer('count')->default(0)->nullable();
            $table->timestamp('report_created_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('months');
    }
};
