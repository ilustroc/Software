<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pagos')) {
            return;
        }

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cartera_id')->constrained('carteras')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('dni', 20);
            $table->string('operacion', 80);
            $table->string('moneda', 20)->nullable();
            $table->date('fecha');
            $table->decimal('monto', 14, 2);
            $table->string('gestor', 150)->nullable();
            $table->string('origen', 30)->default('manual');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cartera_id', 'fecha']);
            $table->index('dni');
            $table->index('operacion');
            $table->index('gestor');
        });
    }

    public function down(): void
    {
        // No-op: keep payment data safe in shared/production databases.
    }
};
