<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pagos') || !Schema::hasColumn('pagos', 'metadata')) {
            return;
        }

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }

    public function down(): void
    {
        // No-op: metadata was removed intentionally from pagos.
    }
};
