<!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><title>Etiquetas de patrimônio A4</title>
<style>
@page { size: A4 portrait; margin: 10mm; }
body { margin: 0; font-family: 'DejaVu Sans', sans-serif; color: #000; }
.pagina { position: relative; height: 267mm; page-break-after: always; }
.pagina.ultima { page-break-after: auto; }
.etiqueta { position: absolute; width: 83.6mm; height: 41.6mm; padding: 3mm; border: .2mm dashed #999; overflow: hidden; }
.qr { position: absolute; left: 3mm; top: 6mm; width: 35mm; height: 35mm; }
.dados { position: absolute; left: 40mm; top: 5mm; width: 45mm; }
.titulo { font-size: 6.5pt; letter-spacing: .5pt; margin-bottom: 3mm; }
.numero { font-size: 11pt; font-weight: bold; margin-bottom: 2mm; }
.nome { font-size: 9pt; line-height: 1.3; height: 17mm; overflow: hidden; word-wrap: break-word; }
.instrucao { font-size: 6.5pt; margin-top: 2mm; }
.rodape { position: absolute; bottom: 0; right: 0; font-size: 7pt; color: #666; }
</style></head><body>
@foreach($paginas as $pagina)
<div class="pagina {{ $loop->last ? 'ultima' : '' }}">
    @foreach($pagina->values() as $indice => $etiqueta)
    <div class="etiqueta" style="left: {{ ($indice % 2) * 100 }}mm; top: {{ intdiv($indice, 2) * 53 }}mm;">
        <img class="qr" src="{{ $etiqueta['qrcode'] }}" alt="QR Code">
        <div class="dados"><div class="titulo">PATRIMÔNIO ESCOLAR</div><div class="numero">{{ $etiqueta['numero'] }}</div><div class="nome">{{ $etiqueta['nome'] }}</div><div class="instrucao">Escaneie para consultar ou abrir chamado.</div></div>
    </div>
    @endforeach
    <div class="rodape">{{ $loop->iteration }} / {{ $paginas->count() }}</div>
</div>
@endforeach
</body></html>
