@extends('layouts.aplicacao', ['publico'=>true])
@section('titulo', 'Convite indisponível')
@section('conteudo')
<section class="cartao bloco-formulario"><h1>Convite indisponível</h1><p>Este link é inválido, expirou, foi revogado ou já foi utilizado. Solicite um novo convite ao administrador.</p><a class="btn btn-primary" href="{{ route('login') }}">Ir para o acesso</a></section>
@endsection
