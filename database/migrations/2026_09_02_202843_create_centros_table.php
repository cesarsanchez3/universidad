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
        Schema::create('centros', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 10)->unique();
            $table->string('telebachillerato', 255);
            $table->string('clave_ct', 100)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('encargado', 255)->nullable();
            $table->string('correo', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centros');
    }
};
