<?php

namespace App\Http\Controllers;

use App\Models\Convite;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConviteController extends Controller
{
    public function store(Request $requisicao): RedirectResponse
    {
        $dados = $requisicao->validate(['email' => 'required|email|max:255|unique:users,email']);
        $token = Str::random(64);
        Convite::create(['email' => $dados['email'], 'token_hash' => hash('sha256', $token), 'criado_por' => $requisicao->user()->id, 'expira_em' => now()->addDays(7)]);

        return redirect()->route('usuarios.index')->with('convite_link', rtrim(config('app.url'), '/').'/convites/'.$token)->with('sucesso', 'Convite gerado. Copie o link e envie à pessoa convidada. Validade: 7 dias.');
    }

    public function show(string $token): Response
    {
        $convite = Convite::where('token_hash', hash('sha256', $token))->first();
        if (! $convite || ! $convite->disponivel() || User::where('email', $convite->email)->exists()) {
            return response()->view('convites.indisponivel', [], 410)->header('Cache-Control', 'no-store');
        }

        return response()->view('convites.cadastro', compact('convite', 'token'))->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function aceitar(Request $requisicao, string $token): RedirectResponse
    {
        $dados = $requisicao->validate(['name' => 'required|string|max:150', 'password' => 'required|string|min:10|max:128|confirmed'], ['password.confirmed' => 'A confirmação da senha não confere.'], ['name' => 'nome', 'password' => 'senha']);
        try {
            DB::transaction(function () use ($token, $dados) {
                $convite = Convite::where('token_hash', hash('sha256', $token))->lockForUpdate()->first();
                abort_unless($convite && $convite->disponivel(), 410, 'Convite inválido, expirado ou já utilizado.');
                if (User::where('email', $convite->email)->exists()) {
                    throw ValidationException::withMessages(['email' => 'Este e-mail já possui uma conta.']);
                }
                User::create(['name' => $dados['name'], 'email' => $convite->email, 'password' => $dados['password']]);
                $convite->update(['utilizado_em' => now()]);
            });
        } catch (UniqueConstraintViolationException $erro) {
            throw ValidationException::withMessages(['email' => 'Este e-mail já possui uma conta.']);
        }

        return redirect()->route('login')->with('sucesso', 'Conta criada. Entre com seu e-mail e senha.');
    }

    public function revogar(Convite $convite): RedirectResponse
    {
        $convite->update(['revogado_em' => now()]);

        return redirect()->route('usuarios.index')->with('sucesso', 'Convite revogado.');
    }
}
