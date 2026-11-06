<div class="grafico {{ $classe }}">
@forelse($grupos as $rotulo=>$quantidade)
    @php($percentual = $total > 0 ? $quantidade / $total * 100 : 0)
    <div class="linha"><div class="legenda"><span class="valor">{{ $quantidade }} itens · {{ number_format($percentual, 1, ',', '.') }}%</span>{{ $rotulo }}</div><div class="trilho"><div class="barra" style="width:{{ number_format($percentual, 4, '.', '') }}%"></div></div></div>
@empty<p class="nota">Sem dados para exibir.</p>@endforelse
</div>
