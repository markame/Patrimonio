<?php

namespace Database\Seeders;

use App\Models\Patrimonio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemonstracaoSeeder extends Seeder
{
    public function run(): void
    {
        $itens = [
            ['Notebook Dell Inspiron', 'Dell', 'Informática', 'Laboratório de informática', 'Bivolt', 'Em uso'],
            ['Projetor PowerLite', 'Epson', 'Audiovisual', 'Sala de aula 03', 'Bivolt', 'Em manutenção'],
            ['Cadeira universitária', 'Plaxmetal', 'Mobiliário', 'Sala de aula 01', 'Não se aplica', 'Em uso'],
            ['Impressora multifuncional', 'HP', 'Informática', 'Secretaria', '127 V', 'Em uso'],
            ['Bebedouro industrial', 'IBBL', 'Eletrodomésticos', 'Pátio coberto', '220 V', 'Em uso'],
            ['Smart TV 50 polegadas', 'Samsung', 'Audiovisual', 'Biblioteca', 'Bivolt', 'Em uso'],
        ];
        foreach ($itens as $indice => [$nome,$marca,$categoria,$localizacao,$voltagem,$situacao]) {
            $codigo = (string) Str::uuid();
            $item = Patrimonio::firstOrCreate(['numero_serie' => 'DEMO-'.($indice + 1)], compact('nome', 'marca', 'categoria', 'localizacao', 'voltagem', 'situacao') + ['numero' => 'TEMP-'.$codigo, 'codigo_publico' => $codigo]);
            if ($item->wasRecentlyCreated) {
                $item->update(['numero' => 'PAT-'.str_pad((string) $item->id, 6, '0', STR_PAD_LEFT)]);
                $item->historicos()->create(['titulo' => 'Patrimônio cadastrado', 'descricao' => 'Item de demonstração registrado em '.$localizacao.'.']);
                if ($situacao === 'Em manutenção') {
                    $chamado = $item->chamados()->create(['protocolo' => 'TEMP-'.Str::uuid(), 'tipo' => 'Mau funcionamento', 'titulo' => 'Projetor não exibe imagem', 'descricao' => 'O projetor liga, mas não apresenta imagem na tela. Verificar conexões e lâmpada.', 'solicitante' => 'Equipe pedagógica (demonstração)', 'prioridade' => 'Alta', 'situacao' => 'Em atendimento']);
                    $chamado->update(['protocolo' => 'OS-'.str_pad((string) $chamado->id, 6, '0', STR_PAD_LEFT)]);
                    $item->historicos()->create(['titulo' => $chamado->protocolo.' · Em atendimento', 'descricao' => 'Verificação do projetor iniciada pela equipe da escola.']);
                }
            }
        }
    }
}
