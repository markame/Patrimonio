@extends('layouts.aplicacao', ['publico'=>true])
@section('titulo', 'Cadastro por convite')
@section('conteudo')
<div class="cabecalho-pagina"><div><h1>Criar sua conta</h1><p>Convite para usuário do sistema. Válido até {{ $convite->expira_em->format('d/m/Y H:i') }}.</p></div></div>
<form method="post" action="{{ route('convites.aceitar', $token) }}" class="cartao bloco-formulario">
    @csrf
    <div class="mb-4"><label class="form-label" for="email">E-mail convidado</label><input class="form-control" type="email" id="email" value="{{ $convite->email }}" readonly></div>
    <div class="mb-4"><label class="form-label" for="name">Nome completo *</label><input class="form-control" id="name" name="name" maxlength="150" value="{{ old('name') }}" autocomplete="name" required></div>
    <div class="mb-4"><label class="form-label" for="password">Senha *</label><input class="form-control" type="password" id="password" name="password" minlength="10" maxlength="128" autocomplete="new-password" required><p class="form-text">Pelo menos 10 caracteres.</p></div>
    <div class="mb-4"><label class="form-label" for="password_confirmation">Confirmar senha *</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="10" maxlength="128" autocomplete="new-password" required></div>
    <button class="btn btn-primary w-100">Criar conta</button>
</form>
@endsection
