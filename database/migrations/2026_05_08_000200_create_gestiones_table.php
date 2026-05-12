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
            $table->string('nombre', 200)->nullable();
            $table->string('value2', 300)->nullable();
            $table->string('value1', 300)->nullable();
            $table->string('fullname', 150)->nullable();
            $table->string('operacion', 100)->nullable();
            $table->dateTime('dateprocessed')->nullable();
            $table->string('callerid', 200)->nullable();
            $table->text('comment')->nullable();
            $table->decimal('monto_promesa', 14, 2)->nullable();
            $table->string('nro_cuota', 50)->nullable();
            $table->dateTime('fecha_promesa')->nullable();
            $table->string('campaign', 120)->nullable();
            $table->string('origen', 50)->default('crm');
            $table->string('legacy_id', 80)->nullable();
            $table->timestamps();

            $table->index('cartera_id');
            $table->index('documento');
            $table->index('operacion');
            $table->index('dateprocessed');
            $table->index('callerid');
            $table->index('value2');
            $table->index('origen');
            $table->unique(['origen', 'legacy_id'], 'gestiones_origen_legacy_id_unique');
        });
    }

    public function down(): void
    {
        // No-op: keep management data safe in shared/production databases.
    }
};
