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
        Schema::create('comprobante', function (Blueprint $table) {
            $table->id();
            
            /* Datos de la persona */
            $table->string('cuit_dni')->nullable();
            $table->string('razon_social')->nullable();
            $table->string('domicilio')->nullable();
            
            /* Datos del comprobante - comercio */
            $table->unsignedBigInteger('tipo_comprobante_id')->nullable();
            $table->integer('punto_venta')->nullable();
            $table->string('nro_comprobante')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('anulacion_id')->nullable();
            //nuevo
            $table->unsignedBigInteger('condicion_iva_receptor_id')->nullable();

            //fechas
            $table->unsignedBigInteger('concepto')->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_servicio_desde')->nullable();
            $table->date('fecha_servicio_hasta')->nullable();
            $table->date('fecha_vencimiento_pago')->nullable();

            /* Montos */
            $table->double('importe_neto')->nullable();
            $table->double('importe_gravado')->nullable();
            $table->double('importe_no_gravado')->nullable();
            $table->double('importe_exento_iva')->nullable();
            $table->double('importe_iva')->nullable();
            $table->double('importe_total_tributos')->nullable();
            $table->double('importe_total')->nullable();

            /* AFIP */
            $table->text('observaciones')->nullable();
            $table->string('cae')->nullable();
            $table->date('fecha_vencimiento_cae')->nullable();
            
            $table->timestamps();
        });
    }
    
    /**
    * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('comprobante');
    }
};
