<?php

namespace App\Models;

use Database\Factories\ConviteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convite extends Model
{
    /** @use HasFactory<ConviteFactory> */
    use HasFactory;

    protected $fillable = ['email', 'token_hash', 'criado_por', 'expira_em', 'utilizado_em', 'revogado_em'];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['expira_em' => 'datetime', 'utilizado_em' => 'datetime', 'revogado_em' => 'datetime'];
    }

    public function disponivel(): bool
    {
        return $this->utilizado_em === null && $this->revogado_em === null && $this->expira_em->isFuture();
    }
}
