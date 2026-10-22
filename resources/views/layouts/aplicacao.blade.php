<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Visão geral') · Escola Patrimônio</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/aplicacao.css') }}">
</head>
<body class="{{ isset($publico) ? 'pagina-publica' : '' }}">
@unless(isset($publico))
<aside class="barra-lateral offcanvas-lg offcanvas-start" tabindex="-1" id="navegacao">
    <button type="button" class="btn-close fechar-menu d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#navegacao" aria-label="Fechar menu"></button>
    <a class="marca" href="{{ route('painel') }}"><span class="marca-simbolo">@include('componentes.icone', ['nome' => 'escola'])</span><span>escola<span class="marca-subtitulo">PATRIMÔNIO</span></span></a>
    <div class="escola-atual"><span class="escola-avatar">E</span><div>Minha escola<small>Gestão patrimonial</small></div><span class="ms-auto text-secondary">⌄</span></div>
    <div class="rotulo-menu">PRINCIPAL</div>
    <nav class="menu">
        @can('gerenciar-usuarios')
        <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'ativo' : '' }}">@include('componentes.icone', ['nome' => 'usuarios']) Usuários</a>
        @endcan
        <a href="{{ route('painel') }}" class="{{ request()->routeIs('painel') ? 'ativo' : '' }}">@include('componentes.icone', ['nome' => 'painel']) Visão geral</a>
        <a href="{{ route('patrimonios.index') }}" class="{{ request()->routeIs('patrimonios.*') ? 'ativo' : '' }}">@include('componentes.icone') Patrimônios</a>
        <a href="{{ route('chamados.index') }}" class="{{ request()->routeIs('chamados.*') ? 'ativo' : '' }}">@include('componentes.icone', ['nome' => 'reparo']) Chamados de reparo</a>
    </nav>
    <div class="perfil"><span class="escola-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->administrador ? 'Administrador' : 'Usuário' }}</small></div><form method="post" action="{{ route('sair') }}" class="ms-auto">@csrf<button class="botao-icone" title="Sair" aria-label="Sair">@include('componentes.icone', ['nome' => 'sair'])</button></form></div>
</aside>
<nav class="navegacao-mobile" aria-label="Navegação principal">
    <a href="{{ route('painel') }}" class="{{ request()->routeIs('painel') ? 'ativo' : '' }}" @if(request()->routeIs('painel')) aria-current="page" @endif>@include('componentes.icone', ['nome'=>'painel'])<span>Início</span></a>
    <a href="{{ route('patrimonios.index') }}" class="{{ request()->routeIs('patrimonios.*') ? 'ativo' : '' }}" @if(request()->routeIs('patrimonios.*')) aria-current="page" @endif>@include('componentes.icone', ['nome'=>'caixa'])<span>Patrimônios</span></a>
    <a href="{{ route('chamados.index') }}" class="{{ request()->routeIs('chamados.*') ? 'ativo' : '' }}" @if(request()->routeIs('chamados.*')) aria-current="page" @endif>@include('componentes.icone', ['nome'=>'reparo'])<span>Chamados</span></a>
</nav>
@endunless
<div class="area-principal">
    <header class="barra-superior">
        @unless(isset($publico))<button class="botao-icone d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#navegacao" aria-label="Abrir menu">@include('componentes.icone', ['nome' => 'menu'])</button>@endunless
        <span>{{ isset($publico) ? 'Escola Patrimônio' : 'Gestão escolar' }} <span class="mx-2 text-secondary">/</span> <strong>@yield('titulo', 'Visão geral')</strong></span>
        <span class="data-cabecalho ms-auto">{{ now()->translatedFormat('d \d\e F, Y') }}</span><span class="selo-ambiente">● Sistema patrimonial</span>
    </header>
    <main class="conteudo">
        @if(session('sucesso'))<div class="alert alert-success" role="status">{{ session('sucesso') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Revise os campos abaixo.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>@endif
        @yield('conteudo')
        <footer class="rodape">Escola Patrimônio </footer>
    </main>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
@stack('scripts')
</body></html>
