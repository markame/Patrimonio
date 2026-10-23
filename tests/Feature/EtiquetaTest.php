<?php

namespace Tests\Feature;

use App\Models\Patrimonio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EtiquetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_exige_login(): void
    {
        $this->get('/patrimonios/etiquetas-a4.pdf')->assertRedirect('/entrar');
    }

    public function test_sem_patrimonios_retorna_mensagem(): void
    {
        $this->actingAs(User::factory()->create())->get('/patrimonios/etiquetas-a4.pdf')->assertRedirect(route('patrimonios.index'))->assertSessionHasErrors('etiquetas');
    }

    public function test_pdf_a4_gera_qrcodes_em_ordem_numerica_com_varias_paginas(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([11, 2, 10, 1, 9, 8, 7, 6, 5, 4, 3] as $numero) {
            Patrimonio::create(['numero' => 'PAT-'.$numero, 'codigo_publico' => (string) Str::uuid(), 'nome' => 'Projetor e equipamento de informática com nome longo para verificar a impressão das etiquetas na escola', 'marca' => 'Marca', 'voltagem' => 'Bivolt', 'localizacao' => 'Sala 01', 'categoria' => 'Audiovisual', 'situacao' => 'Em uso']);
        }
        $resposta = $this->get('/patrimonios/etiquetas-a4.pdf');
        $resposta->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('Content-Disposition', 'attachment; filename="etiquetas-patrimonios-a4.pdf"');
        $this->assertStringStartsWith('%PDF-', $resposta->getContent());
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page\b/', $resposta->getContent()));
        $this->assertStringContainsString('/MediaBox [0.000 0.000 595.280 841.890]', $resposta->getContent());
    }
}
