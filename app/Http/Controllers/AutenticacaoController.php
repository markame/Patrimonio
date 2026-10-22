<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AutenticacaoController extends Controller
{
    public function entrar(Request $requisicao)
    {
        $dados = $requisicao->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($dados)) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha incorretos.']);
        }
        $requisicao->session()->regenerate();

        return redirect()->intended(route('painel'));
    }

    public function sair(Request $requisicao)
    {
        Auth::logout();
        $requisicao->session()->invalidate();
        $requisicao->session()->regenerateToken();

        return redirect()->route('login');
    }
}
