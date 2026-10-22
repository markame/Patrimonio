<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patrimonio extends Model
{
    protected $table = 'patrimonios';

    protected $guarded = ['id'];

    public function chamados()
    {
        return $this->hasMany(Chamado::class);
    }

    public function historicos()
    {
        return $this->hasMany(Historico::class)->latest();
    }
}
