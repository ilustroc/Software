<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gestiones')) {
            return;
        }

        Schema::create('gestiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cartera_id')->nullable()->constrained('carteras')->nullOnDelete()->cascadeOnUpdate();
            $table->string('documento', 30)->nullable();
            $table->string('licencia_id', 60)->nullable();
            $table->string('socio', 180)->nullable();
            $table->string('cliente', 180)->nullable();
            $table->string('tipificacion', 200)->nullable();
            $table->string('resultado', 120)->nullable();
            $table->string('asesor', 150)->nullable();
            $table->string('operacion', 80)->nullable();
            $table->string('entidad', 120)->nullable();
            $table->string('subcartera', 120)->nullable();
            $table->dateTime('fecha_gestion')->nullable();
            $table->dateTime('fecha_agenda')->nullable();
            $table->string('telefono', 40)->nullable();
            $table->text('comentario')->nullable();
            $table->decimal('monto_promesa', 14, 2)->nullable();
            $table->unsignedInteger('nro_cuotas')->nullable();
            $table->dateTime('fecha_promesa')->nullable();
            $table->string('campaign', 120)->nullable();
            $table->string('origen', 30)->default('crm');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['cartera_id', 'fecha_gestion']);
            $table->index('documento');
            $table->index('telefono');
            $table->index('tipificacion');
        });
    }

    public function down(): void
    {
        // No-op: keep management data safe in shared/production databases.
    }
};
