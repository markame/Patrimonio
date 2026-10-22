<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_precisa_entrar_para_acessar_cadastro(): void
    {
        $this->get('/usuarios')->assertRedirect('/entrar');
        $this->get('/usuarios/create')->assertRedirect('/entrar');
        $this->post('/usuarios', [])->assertRedirect('/entrar');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_usuario_comum_nao_pode_criar_usuarios_nem_enviando_perfil_admin(): void
    {
        $usuario = User::factory()->create();
        $this->assertFalse(Gate::forUser($usuario)->allows('gerenciar-usuarios'));
        $this->actingAs($usuario)->get('/usuarios')->assertForbidden();
        $this->get('/usuarios/create')->assertForbidden();
        $this->post('/usuarios', ['name' => 'Tentativa', 'email' => 'tentativa@example.test', 'password' => 'senha-segura-123', 'password_confirmation' => 'senha-segura-123', 'administrador' => true])->assertForbidden();
        $this->assertDatabaseCount('users', 1);
        $this->get('/')->assertOk()->assertDontSee(route('usuarios.index'));
    }

    public function test_administrador_cria_usuario_com_senha_protegida_sem_conceder_perfil_admin(): void
    {
        $administrador = User::factory()->create(['administrador' => true]);
        $this->assertTrue(Gate::forUser($administrador)->allows('gerenciar-usuarios'));
        $this->actingAs($administrador)->get('/usuarios/create')->assertOk();
        $this->post('/usuarios', ['name' => 'Equipe da escola', 'email' => 'equipe@example.test', 'password' => 'senha-segura-123', 'password_confirmation' => 'senha-segura-123', 'administrador' => true])->assertRedirect(route('usuarios.index'))->assertSessionHasNoErrors();
        $novoUsuario = User::where('email', 'equipe@example.test')->firstOrFail();
        $this->assertSame('Equipe da escola', $novoUsuario->name);
        $this->assertFalse($novoUsuario->administrador);
        $this->assertTrue(Hash::check('senha-segura-123', $novoUsuario->password));
        $this->get('/usuarios')->assertOk()->assertSee('equipe@example.test');
        $this->post('/sair');
        $this->post('/entrar', ['email' => 'equipe@example.test', 'password' => 'senha-segura-123'])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($novoUsuario);
        $this->get('/usuarios/create')->assertForbidden();
    }

    public function test_cadastro_valida_campos_email_unico_e_confirmacao(): void
    {
        $administrador = User::factory()->create(['administrador' => true]);
        $this->actingAs($administrador)->post('/usuarios', [])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->post('/usuarios', ['name' => 'Equipe', 'email' => $administrador->email, 'password' => 'curta', 'password_confirmation' => 'outra'])->assertSessionHasErrors(['email', 'password']);
        $this->post('/usuarios', ['name' => 'Equipe', 'email' => 'equipe@example.test', 'password' => 'senha-segura-123', 'password_confirmation' => 'nao-confere'])->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_comando_cria_somente_o_primeiro_administrador(): void
    {
        $this->artisan('escola:administrador', ['email' => 'primeiro@example.test'])->expectsQuestion('Senha (mínimo de 10 caracteres)', 'senha-segura-123')->assertSuccessful();
        $this->assertDatabaseHas('users', ['email' => 'primeiro@example.test', 'administrador' => true]);
        $this->artisan('escola:administrador', ['email' => 'outro@example.test'])->assertFailed();
        $this->assertDatabaseCount('users', 1);
    }
}
