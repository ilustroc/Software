<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createBaseTelefonosHistorico();
        $this->createTelHistoricoStaging();
        $this->createCartera1y2();
        $this->createLegacyGestiones();
        $this->createJobsTable();
        $this->ensureTipificacionesOrigen();
    }

    public function down(): void
    {
        // No-op: these tables may already exist with production data.
    }

    private function createBaseTelefonosHistorico(): void
    {
        if (Schema::hasTable('Base_Telefonos_Historico')) {
            return;
        }

        Schema::create('Base_Telefonos_Historico', function (Blueprint $table) {
            $table->char('documento', 8);
            $table->char('telefono', 9);
            $table->string('origen', 20);
            $table->char('fecha_act', 6);
            $table->primary(['documento', 'telefono']);
        });
    }

    private function createTelHistoricoStaging(): void
    {
        if (Schema::hasTable('Tel_Historico_Staging')) {
            return;
        }

        Schema::create('Tel_Historico_Staging', function (Blueprint $table) {
            $table->char('documento', 8)->nullable();
            $table->char('telefono', 9)->nullable();
            $table->string('origen', 20)->nullable();
            $table->char('fecha_act', 6)->nullable();
        });
    }

    private function createCartera1y2(): void
    {
        if (Schema::hasTable('Cartera_1y2')) {
            return;
        }

        Schema::create('Cartera_1y2', function (Blueprint $table) {
            $table->id();
            $table->string('CARTERA', 20);
            $table->string('CUENTA', 50)->nullable();
            $table->string('DNI', 15);
            $table->string('OPERACION', 50);
            $table->string('TITULAR', 150);
            $table->string('ANIO_CASTIGO', 4)->nullable();
            $table->string('MONEDA', 20)->nullable();
            $table->string('ENTIDAD', 100)->nullable();
            $table->string('PRODUCTO', 100)->nullable();
            $table->string('COSECHA', 50)->nullable();
            $table->string('NRO_TARJETA', 30)->nullable();
            $table->string('DEPARTAMENTO', 80)->nullable();
            $table->date('FECHA_COMPRA')->nullable();
            $table->integer('EDAD')->nullable();
            $table->string('SEXO', 10)->nullable();
            $table->string('ESTADO_CIVIL', 30)->nullable();
            $table->decimal('CAPITAL', 15, 2)->nullable();
            $table->decimal('INTERES', 15, 2)->nullable();
            $table->decimal('DEUDA_TOTAL', 15, 2)->nullable();

            $table->unique(['DNI', 'OPERACION'], 'cartera_1y2_uni_dni_operacion');
            $table->index('DNI', 'cartera_1y2_idx_dni');
            $table->index('OPERACION', 'cartera_1y2_idx_operacion');
            $table->index('CARTERA', 'cartera_1y2_idx_cartera');
        });
    }

    private function createLegacyGestiones(): void
    {
        if (!Schema::hasTable('Gestiones_1y2')) {
            Schema::create('Gestiones_1y2', function (Blueprint $table) {
                $table->id();
                $table->string('documento', 15)->nullable();
                $table->string('nombre')->nullable();
                $table->string('value2', 100)->nullable();
                $table->string('value1', 100)->nullable();
                $table->string('fullname', 100)->nullable();
                $table->string('operacion', 100);
                $table->string('entidad')->nullable();
                $table->string('cartera')->nullable();
                $table->timestamp('dateprocessed')->nullable();
                $table->dateTime('fechaAgenda')->nullable();
                $table->string('callerid', 100)->nullable();
                $table->string('comment', 500)->nullable();
                $table->double('pagar_por_cuota')->nullable();
                $table->string('nroCuotas', 100)->nullable();
                $table->dateTime('fecha_promesa')->nullable();
                $table->string('campaign', 100)->nullable();

                $table->index('operacion', 'g12_idx_operacion');
                $table->index('cartera', 'g12_idx_cartera');
                $table->index('dateprocessed', 'g12_idx_dateproc');
                $table->index('fecha_promesa', 'g12_idx_fecha_promo');
                $table->index('documento', 'g12_idx_documento');
                $table->index('callerid', 'g12_idx_callerid');
            });
        }

        if (!Schema::hasTable('Gestiones_APDAYC')) {
            Schema::create('Gestiones_APDAYC', function (Blueprint $table) {
                $table->id();
                $table->string('documento', 100)->nullable();
                $table->string('LIC_ID', 100)->nullable();
                $table->string('socio', 150)->nullable();
                $table->string('value2', 100)->nullable();
                $table->string('value1', 100)->nullable();
                $table->string('fullname', 100)->nullable();
                $table->dateTime('fechaAgenda')->nullable();
                $table->dateTime('dateprocessed')->nullable();
                $table->string('callerid', 100)->nullable();
                $table->string('comment', 500)->nullable();
                $table->decimal('montoPromesa', 15, 2)->nullable();
                $table->integer('nroCuota')->nullable();
                $table->dateTime('fecha_promesa')->nullable();
                $table->string('campaign', 100)->nullable();

                $table->index('documento', 'gapdayc_idx_documento');
                $table->index('dateprocessed', 'gapdayc_idx_dateprocessed');
                $table->index('campaign', 'gapdayc_idx_campaign');
                $table->index('LIC_ID', 'gapdayc_idx_lic_id');
            });
        }

        if (!Schema::hasTable('Gestiones_Propia3')) {
            Schema::create('Gestiones_Propia3', function (Blueprint $table) {
                $table->id();
                $table->string('documento', 100)->nullable();
                $table->string('nombre', 200)->nullable();
                $table->string('value2', 100)->nullable();
                $table->string('value1', 100)->nullable();
                $table->string('fullname', 100)->nullable();
                $table->string('operacion', 100);
                $table->string('ctl')->nullable();
                $table->timestamp('dateprocessed')->nullable();
                $table->dateTime('fechaAgenda')->nullable();
                $table->string('callerid', 100)->nullable();
                $table->string('comment', 500)->nullable();
                $table->double('pagar_por_cuota')->nullable();
                $table->string('nroCuotas', 100)->nullable();
                $table->dateTime('fecha_promesa')->nullable();
                $table->string('campaign', 100)->nullable();

                $table->index('operacion', 'gp3_idx_operacion');
                $table->index('dateprocessed', 'gp3_idx_dateproc');
                $table->index('fecha_promesa', 'gp3_idx_fecha_promo');
                $table->index('documento', 'gp3_idx_documento');
                $table->index('callerid', 'gp3_idx_callerid');
                $table->index(['documento', 'dateprocessed', 'comment', 'id'], 'gp3_idx_dup');
            });
        }

        if (!Schema::hasTable('Gestiones_Propia4')) {
            Schema::create('Gestiones_Propia4', function (Blueprint $table) {
                $table->id();
                $table->string('documento', 150)->nullable();
                $table->string('cliente', 200)->nullable();
                $table->string('value2', 300)->nullable();
                $table->string('value1', 300)->nullable();
                $table->string('fullname', 100)->nullable();
                $table->string('operacion', 50)->nullable();
                $table->string('entidad', 150)->nullable();
                $table->timestamp('dateprocessed')->nullable();
                $table->dateTime('fechaAgenda')->nullable();
                $table->string('callerid', 200)->nullable();
                $table->string('comment', 2000)->nullable();
                $table->double('importe_financiamiento')->nullable();
                $table->string('nroCuotas', 150)->nullable();
                $table->dateTime('fecha_promesa')->nullable();
                $table->string('campaign', 128)->nullable();

                $table->index('operacion', 'gp4_idx_operacion');
                $table->index('dateprocessed', 'gp4_idx_dateproc');
                $table->index('fecha_promesa', 'gp4_idx_fecha_promo');
                $table->index('documento', 'gp4_idx_documento');
                $table->index('callerid', 'gp4_idx_callerid');
            });
        }
    }

    private function createJobsTable(): void
    {
        if (Schema::hasTable('jobs')) {
            return;
        }

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue');
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');

            $table->index('queue', 'jobs_queue_index');
        });
    }

    private function ensureTipificacionesOrigen(): void
    {
        if (!Schema::hasTable('tipificaciones') || Schema::hasColumn('tipificaciones', 'origen')) {
            return;
        }

        Schema::table('tipificaciones', function (Blueprint $table) {
            $table->string('origen', 25)->nullable()->after('peso');
        });
    }
};
