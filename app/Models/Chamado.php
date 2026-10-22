<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chamado extends Model
{
    protected $table = 'chamados';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['concluido_em' => 'datetime'];
    }

    public function patrimonio()
    {
        return $this->belongsTo(Patrimonio::class);
    }
}
