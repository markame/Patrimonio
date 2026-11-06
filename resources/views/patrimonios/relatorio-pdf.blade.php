<!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><title>Relatório patrimonial</title>
<style>
@page { size: A4 portrait; margin: 16mm 12mm 20mm; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #243c35; line-height: 1.45; }
h1 { font-size: 23pt; margin: 3mm 0; }
h2 { font-size: 15pt; margin: 0 0 6mm; }
h3 { font-size: 11pt; margin: 6mm 0 3mm; }
.rotulo { font-size: 8pt; letter-spacing: 1pt; color: #18765b; }
.subtitulo { color: #64776b; font-size: 8pt; margin-bottom: 7mm; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
thead { display: table-header-group; }
tr { page-break-inside: avoid; }
th { background: #eaf2ed; text-align: left; padding: 3mm 1.5mm; font-size: 7pt; }
td { padding: 3mm 1.5mm; border-bottom: .2mm solid #dfe7e0; vertical-align: top; font-size: 7.5pt; word-wrap: break-word; }
.muted { color: #64776b; font-size: 7pt; }
.resumo { page-break-before: always; }
.indicadores { margin: 7mm 0; }
.indicadores td { width: 25%; background: #f2f6f2; padding: 4mm; border: 1mm solid white; }
.quantidade { font-size: 24pt; font-weight: bold; color: #18765b; }
.grafico { margin: 3mm 0 7mm; }
.linha { margin-bottom: 4mm; page-break-inside: avoid; }
.legenda { font-size: 9pt; margin-bottom: 1.5mm; }
.valor { float: right; color: #536d5d; }
.trilho { background: #edf1ed; height: 5mm; }
.barra { background: #18765b; height: 5mm; }
.categorias .barra { background: #547cad; }
.vazio { padding: 10mm; text-align: center; color: #64776b; }
.nota { font-size: 8pt; color: #64776b; margin-top: 5mm; }
</style></head><body>
<div class="rotulo">ESCOLA PATRIMÔNIO</div>
<h1>Relatório patrimonial</h1>
<div class="subtitulo">Emitido em {{ $emitidoEm->format('d/m/Y \à\s H:i') }} · Todos os patrimônios · {{ $total }} {{ $total === 1 ? 'item' : 'itens' }}<br>Ordenação crescente pelo número do patrimônio.</div>
<table>
    <thead><tr><th style="width:16%">Patrimônio</th><th style="width:24%">Item / Marca</th><th style="width:18%">Série / Voltagem</th><th style="width:16%">Localização</th><th style="width:13%">Situação</th><th style="width:13%">Cadastro</th></tr></thead>
    <tbody>
        @forelse($patrimonios as $patrimonio)
        <tr><td><strong>{{ $patrimonio->numero }}</strong></td><td>{{ $patrimonio->nome }}<br><span class="muted">{{ $patrimonio->marca }}</span></td><td>{{ $patrimonio->numero_serie ?: 'Não informada' }}<br><span class="muted">{{ $patrimonio->voltagem }}</span></td><td>{{ $patrimonio->localizacao }}</td><td>{{ $patrimonio->situacao }}</td><td>{{ $patrimonio->created_at?->format('d/m/Y') ?: 'Não informada' }}</td></tr>
        @empty<tr><td colspan="6" class="vazio">Nenhum patrimônio cadastrado.</td></tr>@endforelse
    </tbody>
</table>
<div class="resumo">
    <div class="rotulo">QUANTIDADES E DISTRIBUIÇÃO</div>
    <h1>Resumo do patrimônio</h1>
    <p class="subtitulo">Os totais correspondem à lista completa deste relatório na data de emissão.</p>
    <table class="indicadores"><tr>
        <td>Total de itens<div class="quantidade">{{ $total }}</div></td>
        <td>Em uso<div class="quantidade">{{ $porSituacao['Em uso'] }}</div></td>
        <td>Em manutenção<div class="quantidade">{{ $porSituacao['Em manutenção'] }}</div></td>
        <td>Baixados<div class="quantidade">{{ $porSituacao['Baixado'] }}</div></td>
    </tr></table>
    <h3>Distribuição por situação</h3>
    @include('patrimonios.grafico-relatorio', ['grupos'=>$porSituacao, 'classe'=>''])
    <h3>Distribuição por categoria</h3>
    @include('patrimonios.grafico-relatorio', ['grupos'=>$porCategoria, 'classe'=>'categorias'])
    <h3>Quantidade por localização</h3>
    <p class="nota">{{ $porLocalizacao->count() }} {{ $porLocalizacao->count() === 1 ? 'localização' : 'localizações' }} · {{ $porCategoria->count() }} {{ $porCategoria->count() === 1 ? 'categoria' : 'categorias' }}</p>
    <table><thead><tr><th style="width:64%">Localização</th><th style="width:18%">Quantidade</th><th style="width:18%">Percentual</th></tr></thead><tbody>
    @forelse($porLocalizacao as $localizacao=>$quantidade)
    <tr><td>{{ $localizacao }}</td><td>{{ $quantidade }}</td><td>{{ number_format($total > 0 ? $quantidade / $total * 100 : 0, 1, ',', '.') }}%</td></tr>
    @empty<tr><td colspan="3">Nenhuma localização cadastrada.</td></tr>@endforelse
    </tbody></table>
    <p class="nota">Percentuais calculados sobre {{ $total }} itens. Diferenças na soma dos percentuais podem ocorrer por arredondamento.</p>
</div>
</body></html>
