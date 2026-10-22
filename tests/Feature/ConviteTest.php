<?php

namespace Tests\Feature;

use App\Models\Convite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_somente_administrador_gera_e_revoga_convites(): void
    {
        $this->post('/convites', ['email' => 'novo@example.test'])->assertRedirect('/entrar');
        $comum = User::factory()->create();
        $convite = Convite::factory()->create();
        $this->actingAs($comum)->post('/convites', ['email' => 'novo@example.test'])->assertForbidden();
        $this->post('/convites/'.$convite->id.'/revogar')->assertForbidden();
        $admin = User::factory()->create(['administrador' => true]);
        $this->actingAs($admin)->post('/convites', ['email' => 'novo@example.test'])->assertSessionHasNoErrors()->assertSessionHas('convite_link');
        $link = session('convite_link');
        $this->assertDatabaseHas('convites', ['email' => 'novo@example.test', 'token_hash' => hash('sha256', basename($link))]);
        $this->get('/usuarios')->assertOk()->assertSee('novo@example.test');
        $this->post('/convites/'.$convite->id.'/revogar')->assertRedirect(route('usuarios.index'));
        $this->assertNotNull($convite->fresh()->revogado_em);
    }

    public function test_convidado_cria_conta_com_email_do_convite_e_sem_perfil_admin(): void
    {
        $token = 'token-de-teste';
        $convite = Convite::factory()->create(['email' => 'pessoa@example.test', 'token_hash' => hash('sha256', $token)]);
        $this->get('/convites/'.$token)->assertOk()->assertSee('pessoa@example.test');
        $this->post('/convites/'.$token, ['name' => 'Pessoa convidada', 'password' => 'senha-segura-123', 'password_confirmation' => 'senha-segura-123', 'email' => 'outro@example.test', 'administrador' => true])->assertRedirect('/entrar')->assertSessionHasNoErrors();
        $usuario = User::where('email', 'pessoa@example.test')->firstOrFail();
        $this->assertFalse($usuario->administrador);
        $this->assertTrue(Hash::check('senha-segura-123', $usuario->password));
        $this->assertNotNull($convite->fresh()->utilizado_em);
        $this->get('/convites/'.$token)->assertStatus(410);
        $this->post('/convites/'.$token, ['name' => 'Outra pessoa', 'password' => 'senha-segura-123', 'password_confirmation' => 'senha-segura-123'])->assertStatus(410);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_convite_expirado_revogado_ou_inexistente_nao_cadastra(): void
    {
        foreach (['expirado', 'revogado', 'inexistente'] as $token) {
            if ($token !== 'inexistente') {
                Convite::factory()->create(['token_hash' => hash('sha256', $token), 'expira_em' => $token === 'expirado' ? now()->subMinute() : now()->addDay(), 'revogado_em' => $token === 'revogado' ? now() : null]);
            }
            $this->get('/convites/'.$token)->assertStatus(410);
            $this->post('/convites/'.$token, ['name' => 'Pessoa', 'password' => 'senha-segura-123', 'password_confirmation' => 'senha-segura-123'])->assertStatus(410);
        }
        $this->assertDatabaseMissing('users', ['name' => 'Pessoa']);
    }

    public function test_validacao_nao_consumome_convite(): void
    {
        $convite = Convite::factory()->create(['token_hash' => hash('sha256', 'teste')]);
        $this->post('/convites/teste', ['name' => 'Pessoa', 'password' => 'senha-segura-123', 'password_confirmation' => 'outra'])->assertSessionHasErrors('password');
        $this->assertNull($convite->fresh()->utilizado_em);
        $this->assertDatabaseCount('users',1);
    }
}
