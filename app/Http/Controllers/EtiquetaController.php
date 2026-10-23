<?php

namespace App\Http\Controllers;

use App\Models\Patrimonio;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class EtiquetaController extends Controller
{
    public function baixar(): Response|RedirectResponse
    {
        $patrimonios = Patrimonio::query()->get(['id', 'numero', 'nome', 'codigo_publico'])->sortBy('numero', SORT_NATURAL)->values();
        if ($patrimonios->isEmpty()) {
            return redirect()->route('patrimonios.index')->withErrors(['etiquetas' => 'Cadastre ao menos um patrimônio para gerar as etiquetas.']);
        }

        $gravador = new PngWriter;
        $etiquetas = $patrimonios->map(function (Patrimonio $patrimonio) use ($gravador): array {
            $endereco = rtrim(config('app.url'), '/').'/p/'.$patrimonio->codigo_publico;

            return [
                'numero' => $patrimonio->numero,
                'nome' => $patrimonio->nome,
                'qrcode' => $gravador->write(new QrCode(data: $endereco, size: 300, margin: 32))->getDataUri(),
            ];
        });

        $opcoes = new Options;
        $opcoes->set('isRemoteEnabled', false);
        $opcoes->set('isPhpEnabled', false);
        $opcoes->set('isJavascriptEnabled', false);
        $opcoes->set('defaultFont', 'DejaVu Sans');
        $opcoes->set('fontCache', storage_path('framework/cache'));
        $opcoes->set('tempDir', storage_path('app'));
        $pdf = new Dompdf($opcoes);
        $pdf->setPaper('A4', 'portrait');
        $pdf->loadHtml(view('patrimonios.etiquetas-pdf', ['paginas' => $etiquetas->chunk(10)])->render(), 'UTF-8');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="etiquetas-patrimonios-a4.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
