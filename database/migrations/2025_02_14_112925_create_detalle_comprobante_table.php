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
        Schema::create('detalle_comprobante', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comprobante_id')->nullable();
            $table->string('codigo')->nullable();
            $table->string('descripcion')->nullable();
            $table->double('cantidad');
            $table->string('unidad_medida')->nullable();
            $table->double('importe_unitario');
            $table->double('porcentaje_descuento')->nullable();
            $table->double('importe_descuento')->nullable();

            $table->unsignedBigInteger('iva_id')->nullable();
            $table->double('importe_iva')->nullable();
            $table->double('importe_subtotal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_comprobante');
    }
};
