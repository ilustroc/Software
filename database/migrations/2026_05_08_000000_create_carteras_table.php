<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('carteras')) {
            return;
        }

        Schema::create('carteras', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nombre', 120);
            $table->string('codigo', 30)->nullable()->unique();
            $table->unsignedSmallInteger('sistema')->nullable();
            $table->boolean('activa')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // No-op: keep cartera configuration safe in shared/production databases.
    }
};
