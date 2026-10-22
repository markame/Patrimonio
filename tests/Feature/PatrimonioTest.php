<?php

namespace Tests\Feature;

use App\Models\Patrimonio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PatrimonioTest extends TestCase
{
    use RefreshDatabase;

    private function dados(): array
    {
        return ['nome' => 'Projetor', 'marca' => 'Epson', 'numero_serie' => 'SERIE-123', 'voltagem' => 'Bivolt', 'localizacao' => 'Sala 03', 'categoria' => 'Audiovisual', 'situacao' => 'Em uso', 'observacoes' => 'Anotação interna reservada'];
    }

    private function cadastrar(): Patrimonio
    {
        $this->actingAs(User::factory()->create())->post('/patrimonios', $this->dados())->assertSessionHasNoErrors();

        return Patrimonio::firstOrFail();
    }

    public function test_cadastro_gera_numero_foto_historico_e_etiqueta(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create())->post('/patrimonios', [...$this->dados(), 'foto' => UploadedFile::fake()->image('projetor.jpg')])->assertSessionHasNoErrors()->assertRedirect();
        $item = Patrimonio::firstOrFail();
        $this->assertSame('PAT-000001', $item->numero);
        Storage::disk('public')->assertExists($item->foto);
        $this->assertSame(1, $item->historicos()->count());
        $this->get('/')->assertOk()->assertSee('Projetor');
        $this->get('/patrimonios')->assertOk()->assertSee('PAT-000001');
        $this->get('/patrimonios/'.$item->id)->assertOk();
        $this->get('/patrimonios/'.$item->id.'/edit')->assertOk();
        $this->get('/patrimonios/'.$item->id.'/etiqueta')->assertOk()->assertSee('data:image/svg+xml', false)->assertSee('/p/'.$item->codigo_publico);
    }

    public function test_visitante_abre_chamado_sem_expor_contato_no_historico_publico(): void
    {
        $item = $this->cadastrar();
        auth()->logout();
        $this->get('/p/'.$item->codigo_publico)->assertOk()->assertDontSee('Anotação interna reservada');
        $this->post('/p/'.$item->codigo_publico.'/chamados', [
            'tipo' => 'Mau funcionamento', 'titulo' => 'Equipamento não liga', 'descricao' => 'Ao pressionar o botão, não acontece nada.', 'solicitante' => 'Nome reservado', 'contato' => 'contato@privado.test', 'prioridade' => 'Urgente', 'situacao' => 'Concluído',
        ])->assertSessionHasNoErrors()->assertSessionHas('sucesso');
        $this->assertDatabaseHas('chamados', ['protocolo' => 'OS-000001', 'situacao' => 'Aberto', 'prioridade' => 'Normal']);
        $this->get('/p/'.$item->codigo_publico)->assertOk()->assertSee('OS-000001')->assertDontSee('Nome reservado')->assertDontSee('contato@privado.test');
        $this->get('/chamados/1')->assertRedirect('/entrar');
    }

    public function test_conclusao_exige_solucao_e_registra_historico(): void
    {
        $item = $this->cadastrar();
        $chamado = $item->chamados()->create(['protocolo' => 'OS-000001', 'tipo' => 'Reparo', 'titulo' => 'Falha', 'descricao' => 'Falha no cabo de energia', 'solicitante' => 'Professor']);
        $this->put('/chamados/'.$chamado->id, ['situacao' => 'Concluído', 'prioridade' => 'Alta'])->assertSessionHasErrors('solucao');
        $this->put('/chamados/'.$chamado->id, ['situacao' => 'Concluído', 'prioridade' => 'Alta', 'solucao' => 'Cabo substituído.'])->assertSessionHasNoErrors();
        $this->assertNotNull($chamado->fresh()->concluido_em);
        $this->assertDatabaseHas('historicos', ['patrimonio_id' => $item->id, 'titulo' => 'OS-000001 · Concluído']);
        $this->get('/chamados')->assertOk();
        $this->get('/chamados/'.$chamado->id)->assertOk()->assertSee('Cabo substituído.');
        $this->put('/chamados/'.$chamado->id, ['situacao' => 'Em atendimento', 'prioridade' => 'Normal'])->assertSessionHasNoErrors();
        $this->assertNull($chamado->fresh()->concluido_em);
    }

    public function test_validacao_serie_unica_atualizacao_e_busca(): void
    {
        $item = $this->cadastrar();
        $this->post('/patrimonios', $this->dados())->assertSessionHasErrors('numero_serie');
        $this->put('/patrimonios/'.$item->id, [...$this->dados(), 'localizacao' => 'Biblioteca', 'situacao' => 'Em manutenção', 'numero' => 'ADULTERADO'])->assertSessionHasNoErrors();
        $this->assertSame('PAT-000001', $item->fresh()->numero);
        $this->assertSame(2, $item->historicos()->count());
        $this->get('/patrimonios?busca=Biblioteca')->assertOk()->assertSee('Projetor');
        $this->get('/patrimonios?busca=Inexistente')->assertOk()->assertSee('Nenhum patrimônio encontrado');
        $this->get('/p/codigo-inexistente')->assertNotFound();
    }

    public function test_autenticacao_e_permissoes(): void
    {
        $this->get('/patrimonios/create')->assertRedirect('/entrar');
        $this->post('/patrimonios', $this->dados())->assertRedirect('/entrar');
        $this->get('/entrar')->assertOk();
        $usuario = User::factory()->create(['password' => bcrypt('senha-segura-123')]);
        $this->post('/entrar', ['email' => $usuario->email, 'password' => 'incorreta'])->assertSessionHasErrors('email');
        $this->post('/entrar', ['email' => $usuario->email, 'password' => 'senha-segura-123'])->assertRedirect('/patrimonios/create');
        $this->assertAuthenticatedAs($usuario);
        $this->post('/sair')->assertRedirect('/entrar');
        $this->assertGuest();
    }
}
