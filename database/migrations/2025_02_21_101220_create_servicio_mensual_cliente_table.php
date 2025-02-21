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
        Schema::create('servicio_mensual_cliente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('item_id')->nullable(); //Si es un item, se puede actualizar su valor automaticamente
            $table->integer('cantidad')->nullable();
            $table->text('descripcion');
            $table->unsignedBigInteger('iva_id')->nullable();
            $table->integer('importe_neto');
            $table->integer('importe_total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicio_mensual_cliente');
    }
};
