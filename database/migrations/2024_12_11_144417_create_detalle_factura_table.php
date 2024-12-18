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
        Schema::create('detalle_factura', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->text('descripcion');
            $table->integer('cantidad')->default(1);
            $table->string('unidad')->default('unidades');
            $table->double('precio_unitario');
            $table->double('alicuota_iva')->nullable();
            $table->double('porcentaje_bonificacion')->nullable();
            $table->double('importe_bonificado')->nullable();
            $table->double('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_factura');
    }
};
