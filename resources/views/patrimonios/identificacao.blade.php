@extends('layouts.aplicacao', ['publico'=>true])
@section('titulo', 'Identificação do visitante')
@section('conteudo')
<div class="cabecalho-pagina"><div><h1>Identificação do visitante</h1><p>Informe seu nome e um contato para consultar o patrimônio ou registrar um chamado. Não é necessário ter conta.</p></div></div>
<section class="cartao bloco-formulario">
    <form method="post" action="{{ route('patrimonios.identificar', $codigo) }}">
        @csrf
        <div class="mb-4"><label class="form-label" for="solicitante">Nome completo *</label><input class="form-control" id="solicitante" name="solicitante" value="{{ old('solicitante') }}" minlength="3" maxlength="100" autocomplete="name" required></div>
        <div class="mb-4"><label class="form-label" for="contato">E-mail ou telefone *</label><input class="form-control" id="contato" name="contato" value="{{ old('contato') }}" minlength="5" maxlength="150" required><p class="form-text">Seus dados serão usados para identificar seus chamados e ficam disponíveis somente para a equipe responsável.</p></div>
        <button class="btn btn-primary w-100">Continuar para o patrimônio @include('componentes.icone', ['nome'=>'seta'])</button>
    </form>
</section>
@endsection
