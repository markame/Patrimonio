<?php

namespace App\Http\Controllers;

use App\Models\Chamado;
use App\Models\Patrimonio;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PatrimonioController extends Controller
{
    public function painel()
    {
        return view('painel', [
            'total' => Patrimonio::count(), 'emUso' => Patrimonio::where('situacao', 'Em uso')->count(),
            'emManutencao' => Patrimonio::where('situacao', 'Em manutenção')->count(),
            'abertos' => Chamado::whereIn('situacao', ['Aberto', 'Em atendimento'])->count(),
            'recentes' => Patrimonio::latest()->take(5)->get(),
            'chamados' => Chamado::with('patrimonio')->whereIn('situacao', ['Aberto', 'Em atendimento'])->latest()->take(4)->get(),
        ]);
    }

    public function index(Request $requisicao)
    {
        $requisicao->validate(['busca' => 'nullable|string|max:100', 'situacao' => 'nullable|in:Em uso,Em manutenção,Baixado']);
        $patrimonios = Patrimonio::query()->when($requisicao->busca, fn ($consulta, $busca) => $consulta->where(function ($consulta) use ($busca) {
            $consulta->where('nome', 'like', "%{$busca}%")->orWhere('numero', 'like', "%{$busca}%")->orWhere('localizacao', 'like', "%{$busca}%")->orWhere('numero_serie', 'like', "%{$busca}%");
        }))->when($requisicao->situacao, fn ($consulta, $situacao) => $consulta->where('situacao', $situacao))->latest()->paginate(12)->withQueryString();

        return view('patrimonios.indice', compact('patrimonios'));
    }

    public function create()
    {
        return view('patrimonios.formulario', ['patrimonio' => new Patrimonio]);
    }

    public function edit(Patrimonio $patrimonio)
    {
        return view('patrimonios.formulario', compact('patrimonio'));
    }

    private function validar(Request $requisicao, ?Patrimonio $patrimonio = null): array
    {
        return $requisicao->validate([
            'nome' => 'required|string|max:150', 'marca' => 'required|string|max:100',
            'numero_serie' => ['nullable', 'string', 'max:150', Rule::unique('patrimonios')->ignore($patrimonio)],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'voltagem' => 'required|in:127 V,220 V,Bivolt,Não se aplica',
            'localizacao' => 'required|string|max:150', 'categoria' => 'required|in:Informática,Mobiliário,Eletrodomésticos,Audiovisual,Outros',
            'situacao' => 'required|in:Em uso,Em manutenção,Baixado', 'observacoes' => 'nullable|string|max:3000',
        ]);
    }

    public function store(Request $requisicao)
    {
        $dados = $this->validar($requisicao);
        $foto = $requisicao->hasFile('foto') ? $requisicao->file('foto')->store('patrimonios', 'public') : null;
        try {
            $patrimonio = DB::transaction(function () use ($dados, $foto) {
                $codigo = (string) Str::uuid();
                $patrimonio = Patrimonio::create([...$dados, 'foto' => $foto, 'codigo_publico' => $codigo, 'numero' => 'TEMP-'.$codigo]);
                $patrimonio->update(['numero' => 'PAT-'.str_pad((string) $patrimonio->id, 6, '0', STR_PAD_LEFT)]);
                $patrimonio->historicos()->create(['titulo' => 'Patrimônio cadastrado', 'descricao' => 'Item registrado em '.$patrimonio->localizacao.'. Situação: '.$patrimonio->situacao.'.']);

                return $patrimonio;
            });
        } catch (\Throwable $erro) {
            if ($foto) {
                Storage::disk('public')->delete($foto);
            }
            throw $erro;
        }

        return redirect()->route('patrimonios.show', $patrimonio)->with('sucesso', 'Patrimônio cadastrado! Sua etiqueta já está disponível.');
    }

    public function update(Request $requisicao, Patrimonio $patrimonio)
    {
        $dados = $this->validar($requisicao, $patrimonio);
        $fotoAnterior = $patrimonio->foto;
        unset($dados['foto']);
        $fotoNova = $requisicao->hasFile('foto') ? $requisicao->file('foto')->store('patrimonios', 'public') : null;
        if ($fotoNova) {
            $dados['foto'] = $fotoNova;
        }
        try {
            DB::transaction(function () use ($patrimonio, $dados) {
                $patrimonio->fill($dados);
                $alteracoes = collect($patrimonio->getDirty())->except(['foto', 'observacoes'])->map(fn ($valor, $campo) => str_replace('_', ' ', $campo).': '.($patrimonio->getOriginal($campo) ?: '—').' → '.($valor ?: '—'))->implode('; ');
                $patrimonio->save();
                $patrimonio->historicos()->create(['titulo' => 'Cadastro atualizado', 'descricao' => $alteracoes ?: 'Foto ou dados do cadastro atualizados.']);
            });
        } catch (\Throwable $erro) {
            if ($fotoNova) {
                Storage::disk('public')->delete($fotoNova);
            }
            throw $erro;
        }
        if ($fotoNova && $fotoAnterior) {
            Storage::disk('public')->delete($fotoAnterior);
        }

        return redirect()->route('patrimonios.show', $patrimonio)->with('sucesso', 'Cadastro atualizado.');
    }

    public function show(Patrimonio $patrimonio)
    {
        $patrimonio->load(['historicos', 'chamados' => fn ($consulta) => $consulta->latest()]);

        return view('patrimonios.detalhe', compact('patrimonio'));
    }

    public function publico(string $codigo)
    {
        $patrimonio = Patrimonio::where('codigo_publico', $codigo)->with('historicos')->firstOrFail();

        return view('patrimonios.publico', compact('patrimonio'));
    }

    public function etiqueta(Patrimonio $patrimonio)
    {
        $endereco = rtrim(config('app.url'), '/').'/p/'.$patrimonio->codigo_publico;
        $qrcode = (new SvgWriter)->write(new QrCode(data: $endereco, size: 250, margin: 12))->getDataUri();

        return view('patrimonios.etiqueta', compact('patrimonio', 'qrcode', 'endereco'));
    }
}
