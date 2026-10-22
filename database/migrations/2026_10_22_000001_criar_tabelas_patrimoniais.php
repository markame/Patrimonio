<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrimonios', function (Blueprint $tabela) {
            $tabela->id();
            $tabela->string('numero')->unique();
            $tabela->uuid('codigo_publico')->unique();
            $tabela->string('nome');
            $tabela->string('marca');
            $tabela->string('numero_serie')->nullable()->unique();
            $tabela->string('foto')->nullable();
            $tabela->string('voltagem');
            $tabela->string('localizacao');
            $tabela->string('categoria');
            $tabela->string('situacao')->default('Em uso');
            $tabela->text('observacoes')->nullable();
            $tabela->timestamps();
        });
        Schema::create('chamados', function (Blueprint $tabela) {
            $tabela->id();
            $tabela->foreignId('patrimonio_id')->constrained('patrimonios')->restrictOnDelete();
            $tabela->string('protocolo')->unique();
            $tabela->string('tipo');
            $tabela->string('titulo');
            $tabela->text('descricao');
            $tabela->string('solicitante');
            $tabela->string('contato')->nullable();
            $tabela->string('prioridade')->default('Normal');
            $tabela->string('situacao')->default('Aberto');
            $tabela->text('solucao')->nullable();
            $tabela->timestamp('concluido_em')->nullable();
            $tabela->timestamps();
        });
        Schema::create('historicos', function (Blueprint $tabela) {
            $tabela->id();
            $tabela->foreignId('patrimonio_id')->constrained('patrimonios')->cascadeOnDelete();
            $tabela->string('titulo');
            $tabela->text('descricao');
            $tabela->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historicos');
        Schema::dropIfExists('chamados');
        Schema::dropIfExists('patrimonios');
    }
};
