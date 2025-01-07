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
        Schema::table('factura', function (Blueprint $table) {
            $table->string('razon_social')->nullable()->after('id_cliente');
            $table->text('domicilio')->nullable()->after('razon_social');
            $table->string('cuit')->nullable()->after('domicilio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factura', function (Blueprint $table) {
            $table->dropColumn('razon_social');
            $table->dropColumn('domicilio');
            $table->dropColumn('cuit');
        });
    }
};
