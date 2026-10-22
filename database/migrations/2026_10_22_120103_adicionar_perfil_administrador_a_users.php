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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('administrador')->default(false);
        });

        // Preserva o acesso administrativo da primeira conta já existente.
        $primeiroUsuario = DB::table('users')->orderBy('id')->value('id');
        if ($primeiroUsuario !== null) {
            DB::table('users')->where('id', $primeiroUsuario)->update(['administrador' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('administrador');
        });
    }
};
