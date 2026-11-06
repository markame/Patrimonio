<?php

namespace App\Http\Controllers;

use App\Models\Patrimonio;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

class RelatorioPatrimonialController extends Controller
{
    public function baixar(): Response
    {
        $patrimonios = Patrimonio::query()->get()->sortBy('numero', SORT_NATURAL)->values();
        $total = $patrimonios->count();
        $porSituacao = collect(['Em uso', 'Em manutenção', 'Baixado'])
            ->mapWithKeys(fn (string $situacao): array => [$situacao => $patrimonios->where('situacao', $situacao)->count()]);
        $porCategoria = $patrimonios->countBy('categoria')->sortDesc();
        $porLocalizacao = $patrimonios->countBy('localizacao')->sortDesc();
        $dados = compact('patrimonios', 'total', 'porSituacao', 'porCategoria', 'porLocalizacao');
        $dados['emitidoEm'] = now();

        $opcoes = new Options;
        $opcoes->set('isRemoteEnabled', false);
        $opcoes->set('isPhpEnabled', false);
        $opcoes->set('isJavascriptEnabled', false);
        $opcoes->set('defaultFont', 'DejaVu Sans');
        $opcoes->set('fontCache', storage_path('framework/cache'));
        $opcoes->set('tempDir', storage_path('app'));
        $pdf = new Dompdf($opcoes);
        $pdf->setPaper('A4', 'portrait');
        $pdf->loadHtml(view('patrimonios.relatorio-pdf', $dados)->render(), 'UTF-8');
        $pdf->render();
        $pdf->getCanvas()->page_text(450, 815, 'Página {PAGE_NUM} de {PAGE_COUNT}', null, 8, [0.4, 0.4, 0.4]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="relatorio-patrimonial.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
