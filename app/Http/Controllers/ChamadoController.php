<?php

namespace App\Http\Controllers;

use App\Models\Chamado;
use App\Models\Patrimonio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ChamadoController extends Controller
{
    public function index(Request $requisicao)
    {
        $requisicao->validate(['situacao' => 'nullable|in:Aberto,Em atendimento,Concluído,Cancelado']);
        $chamados = Chamado::with('patrimonio')->when($requisicao->situacao, fn ($consulta, $situacao) => $consulta->where('situacao', $situacao))->latest()->paginate(12)->withQueryString();

        return view('chamados.indice', compact('chamados'));
    }

    public function store(Request $requisicao, string $codigo)
    {
        $patrimonio = Patrimonio::where('codigo_publico', $codigo)->firstOrFail();
        $dados = $requisicao->validate([
            'tipo' => 'required|in:Reparo,Mau funcionamento,Informação de reparo',
            'titulo' => 'required|string|max:150', 'descricao' => 'required|string|min:10|max:3000',
            'solicitante' => 'required|string|max:100', 'contato' => 'nullable|string|max:150',
        ]);
        $chamado = DB::transaction(function () use ($dados, $patrimonio) {
            $chamado = $patrimonio->chamados()->create([...$dados, 'protocolo' => 'TEMP-'.Str::uuid(), 'prioridade' => 'Normal', 'situacao' => 'Aberto']);
            $chamado->update(['protocolo' => 'OS-'.str_pad((string) $chamado->id, 6, '0', STR_PAD_LEFT)]);
            $patrimonio->historicos()->create(['titulo' => 'Chamado aberto · '.$chamado->protocolo, 'descricao' => $chamado->tipo.'. Situação: Aberto.']);

            return $chamado;
        });

        return back()->with('sucesso', 'Relato recebido! Protocolo '.$chamado->protocolo.'. A equipe da escola acompanhará a solicitação.');
    }

    public function show(Chamado $chamado)
    {
        $chamado->load('patrimonio');

        return view('chamados.detalhe', compact('chamado'));
    }

    public function update(Request $requisicao, Chamado $chamado)
    {
        $dados = $requisicao->validate([
            'situacao' => 'required|in:Aberto,Em atendimento,Concluído,Cancelado',
            'prioridade' => 'required|in:Baixa,Normal,Alta,Urgente',
            'solucao' => [Rule::requiredIf($requisicao->situacao === 'Concluído'), 'nullable', 'string', 'max:3000'],
        ]);
        DB::transaction(function () use ($chamado, $dados) {
            $chamado->update([...$dados, 'concluido_em' => $dados['situacao'] === 'Concluído' ? ($chamado->concluido_em ?? now()) : null]);
            $chamado->patrimonio->historicos()->create(['titulo' => $chamado->protocolo.' · '.$dados['situacao'], 'descricao' => 'Atendimento atualizado pela equipe da escola.']);
        });

        return back()->with('sucesso', 'Ordem de serviço atualizada.');
    }
}
