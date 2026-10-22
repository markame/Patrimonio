@extends('layouts.aplicacao')
@section('titulo', 'Usuários')
@section('conteudo')
<div class="cabecalho-pagina">
    <div><h1>Usuários</h1><p>Somente o administrador pode cadastrar usuários.</p></div>
    <a class="btn btn-primary" href="{{ route('usuarios.create') }}">@include('componentes.icone', ['nome'=>'mais']) Novo usuário</a>
</div>
<section class="cartao bloco-formulario mb-4">
    <h2>Convidar usuário</h2>
    <form method="post" action="{{ route('convites.store') }}">@csrf
        <label class="form-label" for="email-convite">E-mail da pessoa convidada *</label>
        <input class="form-control mb-3" type="email" id="email-convite" name="email" maxlength="255" value="{{ old('email') }}" required>
        <p class="form-text">O convite cria um usuário comum. Uso único e validade de 7 dias.</p>
        <button class="btn btn-primary w-100">Gerar link de convite</button>
    </form>
    @if(session('convite_link'))
    <div class="aviso mt-4"><label class="form-label" for="link-convite">Copie este link e envie à pessoa convidada</label><input class="form-control" id="link-convite" value="{{ session('convite_link') }}" readonly onclick="this.select()"><p class="form-text mb-0">O link é exibido somente após a geração. Se precisar de outro link, revogue este convite e gere um novo.</p></div>
    @endif
</section>
<section class="cartao mb-4">
    <div class="cabecalho-cartao"><h2>Últimos convites</h2></div>
    @forelse($convites as $convite)
    <div class="chamado-resumo"><strong>{{ $convite->email }}</strong><p class="mt-2">{{ $convite->utilizado_em ? 'Utilizado' : ($convite->revogado_em ? 'Revogado' : ($convite->disponivel() ? 'Pendente' : 'Expirado')) }} · Validade: {{ $convite->expira_em->format('d/m/Y H:i') }}</p>
        @if($convite->disponivel())<form method="post" action="{{ route('convites.revogar', $convite) }}">@csrf<button class="btn btn-outline-secondary">Revogar convite</button></form>@endif
    </div>
    @empty<div class="vazio">Nenhum convite gerado.</div>@endforelse
</section>
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
