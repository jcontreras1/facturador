<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'es_admin') && ! Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('es_admin', 'is_admin');
            });
        }

        if (! Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false)->after('remember_token');
            });
        }

        if (! DB::table('users')->where('is_admin', true)->exists()) {
            $firstUserId = DB::table('users')->orderBy('id')->value('id');

            if ($firstUserId !== null) {
                DB::table('users')->where('id', $firstUserId)->update(['is_admin' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_admin') && ! Schema::hasColumn('users', 'es_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('is_admin', 'es_admin');
            });
        }
    }
};
