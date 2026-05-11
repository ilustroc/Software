<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('llamadas')) {
            return;
        }

        Schema::create('llamadas', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 30);
            $table->string('telefono', 40)->nullable();
            $table->string('resultado_gestion', 150)->nullable();
            $table->string('resultado', 120)->default('NO CONTACTO');
            $table->dateTime('fecha_gestion')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('fuente', 80)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('dni');
            $table->index('telefono');
            $table->index('fecha_gestion');
            $table->index('fuente');
            $table->index('resultado');
            $table->unique(['fuente', 'dni', 'telefono', 'fecha_gestion', 'resultado_gestion'], 'llamadas_unique_operational_call');
        });
    }

    public function down(): void
    {
        // No-op: keep call data safe in shared/production databases.
    }
};
