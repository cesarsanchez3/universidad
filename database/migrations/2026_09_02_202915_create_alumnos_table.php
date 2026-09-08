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
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('matricula', 20)->unique();
            $table->unsignedBigInteger('centro_id');
            $table->string('estatus', 50)->nullable();
            $table->string('nombre', 255);
            $table->string('paterno', 255)->nullable();
            $table->string('materno', 255)->nullable();
            $table->string('genero', 20)->nullable();
            $table->integer('generacion')->nullable();
            $table->string('municipio_residencia', 255)->nullable();
            $table->string('pais_nacimiento', 255)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamps();

            $table->foreign('centro_id')->references('id')->on('centros')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
