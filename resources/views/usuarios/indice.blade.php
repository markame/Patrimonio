@extends('layouts.aplicacao')
@section('titulo', 'Usuários')
@section('conteudo')
<div class="cabecalho-pagina">
    <div><h1>Usuários</h1><p>Somente o administrador pode cadastrar usuários.</p></div>
    <a class="btn btn-primary" href="{{ route('usuarios.create') }}">@include('componentes.icone', ['nome'=>'mais']) Novo usuário</a>
</div>
<section class="cartao">
    @foreach($usuarios as $usuario)
    <div class="chamado-resumo">
        <div class="d-flex justify-content-between gap-3 align-items-start"><strong>{{ $usuario->name }}</strong><span class="selo">{{ $usuario->administrador ? 'Administrador' : 'Usuário' }}</span></div>
        <p class="mt-2 mb-0" style="overflow-wrap:anywhere">{{ $usuario->email }}</p>
        <small class="text-secondary">Cadastrado em {{ $usuario->created_at->format('d/m/Y') }}</small>
    </div>
    @endforeach
    <div class="px-3 pt-3">{{ $usuarios->links() }}</div>
</section>
@endsection
