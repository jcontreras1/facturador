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
        Schema::table('servicio_mensual_cliente', function (Blueprint $table) {
            $table->double('cantidad')->nullable()->change();
            $table->double('importe_neto')->change();
            $table->double('importe_total')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicio_mensual_cliente', function (Blueprint $table) {
            $table->integer('cantidad')->nullable()->change();
            $table->integer('importe_neto')->change();
            $table->integer('importe_total')->change();
        });
    }
};