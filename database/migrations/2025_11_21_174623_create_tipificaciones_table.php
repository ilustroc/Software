<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('tipificacion', 200);  // nombre de la tipificación
            $table->string('resultado', 100);     // CONTACTO DIRECTO, NO CONTACTO, etc.
            $table->string('mc', 50);             // 1 = CD+, 3A = NC+ ABAND, etc.
            $table->integer('peso');              // 0,1,2,...36
            $table->integer('orden')->default(0); // para moverlas en la lista
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipificaciones');
    }
};
