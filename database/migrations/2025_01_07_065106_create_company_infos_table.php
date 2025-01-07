<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_infos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Aquaphoenix');
            $table->string('address')->default('35 rue Joseph Lagrosilliére');
            $table->string('zip_code')->default('97220');
            $table->string('city')->default('Trinité');
            $table->string('country')->default('Martinique');
            $table->string('phone')->default('+596 696 34 81 12');
            $table->string('email')->default('contact@aquaphoenix.fr');
            $table->string('logo')->default('images/logo.png');
            $table->string('favicon')->default('images/favicon.ico');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_infos');
    }
};
