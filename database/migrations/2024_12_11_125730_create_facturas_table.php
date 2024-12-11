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
        Schema::create('factura', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_afip')->nullable();
            $table->string('nro_factura')->nullable();
            $table->string('tipo_comprobante')->nullable();
            $table->integer('punto_venta')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('pagada')->nullable();
            $table->boolean('anulada')->default(false);
            $table->double('total_neto')->nullable();
            $table->double('total_iva')->nullable();
            $table->double('total');
            $table->text('observaciones')->nullable();
            $table->boolean('enviada_afip')->default(false);
            $table->string('cae')->nullable();
            $table->timestamps();
        });
    }
    
    /**
    * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
