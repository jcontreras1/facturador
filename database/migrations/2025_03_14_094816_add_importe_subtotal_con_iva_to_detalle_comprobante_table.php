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
        Schema::table('detalle_comprobante', function (Blueprint $table) {
            $table->double('importe_subtotal_con_iva')->nullable()->after('importe_subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_comprobante', function (Blueprint $table) {
            $table->dropColumn('importe_subtotal_con_iva');
        });
    }
};
