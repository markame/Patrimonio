<?php

namespace Tests\Feature;

use App\Models\Patrimonio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tests\TestCase;

class RelatorioPatrimonialTest extends TestCase
{
    use RefreshDatabase;

    public function test_relatorio_exige_login(): void
    {
        $this->get('/patrimonios/relatorio.pdf')->assertRedirect('/entrar');
    }

    public function test_relatorio_vazio_exibe_totais_zero_sem_divisao_por_zero(): void
    {
        $dados = [];
        View::composer('patrimonios.relatorio-pdf', function (\Illuminate\View\View $view) use (&$dados): void {
            $dados = $view->getData();
        });
        $resposta = $this->actingAs(User::factory()->create())->get('/patrimonios/relatorio.pdf');
        $resposta->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame(0, $dados['total']);
        $this->assertSame(['Em uso' => 0, 'Em manutenção' => 0, 'Baixado' => 0], $dados['porSituacao']->all());
        $this->assertTrue($dados['porCategoria']->isEmpty());
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page\b/', $resposta->getContent()));
    }

    public function test_relatorio_lista_todos_os_itens_em_ordem_e_calcula_resumo(): void
    {
        for ($numero = 23; $numero >= 1; $numero--) {
            Patrimonio::create(['numero' => 'PAT-'.$numero, 'codigo_publico' => (string) Str::uuid(), 'nome' => 'Computador para laboratório de informática e atividades pedagógicas '.$numero, 'marca' => 'Dell', 'numero_serie' => 'SERIE-'.$numero, 'voltagem' => 'Bivolt', 'localizacao' => $numero % 2 === 0 ? 'Sala 02' : 'Sala 01', 'categoria' => $numero <= 15 ? 'Informática' : 'Audiovisual', 'situacao' => $numero <= 12 ? 'Em uso' : ($numero <= 18 ? 'Em manutenção' : 'Baixado')]);
        }
        $dados = [];
        View::composer('patrimonios.relatorio-pdf', function (\Illuminate\View\View $view) use (&$dados): void {
            $dados = $view->getData();
        });
        $resposta = $this->actingAs(User::factory()->create())->get('/patrimonios/relatorio.pdf');
        $resposta->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('Content-Disposition', 'attachment; filename="relatorio-patrimonial.pdf"');
        $this->assertStringStartsWith('%PDF-', $resposta->getContent());
        $this->assertStringContainsString('/MediaBox [0.000 0.000 595.280 841.890]', $resposta->getContent());
        $this->assertSame(23, $dados['total']);
        $this->assertSame('PAT-1', $dados['patrimonios']->first()->numero);
        $this->assertSame('PAT-2', $dados['patrimonios'][1]->numero);
        $this->assertSame('PAT-23', $dados['patrimonios']->last()->numero);
        $this->assertSame(['Em uso' => 12, 'Em manutenção' => 6, 'Baixado' => 5], $dados['porSituacao']->all());
        $this->assertSame(['Informática' => 15, 'Audiovisual' => 8], $dados['porCategoria']->all());
        $this->assertSame(['Sala 01' => 12, 'Sala 02' => 11], $dados['porLocalizacao']->all());
        if (getenv('RELATORIO_PDF_QA')) {
            file_put_contents(storage_path('app/relatorio-validacao.pdf'), $resposta->getContent());
        }
    }
}
