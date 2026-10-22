@extends('layouts.aplicacao')
@section('titulo', 'Novo usuário')
@section('conteudo')
<div class="cabecalho-pagina"><div><a class="link-pequeno" href="{{ route('usuarios.index') }}">← Voltar aos usuários</a><h1>Novo usuário</h1><p>O novo usuário não terá permissão para cadastrar outras pessoas.</p></div></div>
<form method="post" action="{{ route('usuarios.store') }}">
    @csrf
    <section class="cartao bloco-formulario">
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label" for="name">Nome *</label><input class="form-control" id="name" name="name" value="{{ old('name') }}" maxlength="150" autocomplete="name" required></div>
            <div class="col-md-6"><label class="form-label" for="email">E-mail *</label><input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="off" required></div>
            <div class="col-md-6"><label class="form-label" for="password">Senha *</label><input class="form-control" type="password" id="password" name="password" minlength="10" maxlength="128" autocomplete="new-password" required><div class="form-text">Pelo menos 10 caracteres.</div></div>
            <div class="col-md-6"><label class="form-label" for="password_confirmation">Confirmar senha *</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="10" maxlength="128" autocomplete="new-password" required></div>
        </div>
    </section>
    <div class="acoes-formulario"><a class="btn btn-outline-secondary" href="{{ route('usuarios.index') }}">Cancelar</a><button class="btn btn-primary">Cadastrar usuário</button></div>
</form>
@endsection
