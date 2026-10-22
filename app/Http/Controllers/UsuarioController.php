<?php

namespace App\Http\Controllers;

use App\Models\Convite;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index(): View
    {
        return view('usuarios.indice', ['usuarios' => User::latest('id')->paginate(15), 'convites' => Convite::latest('id')->take(20)->get()]);
    }

    public function create(): View
    {
        return view('usuarios.formulario');
    }

    public function store(Request $requisicao): RedirectResponse
    {
        $dados = $requisicao->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:10|max:128|confirmed',
        ], ['password.confirmed' => 'A confirmação da senha não confere.'], ['name' => 'nome', 'email' => 'e-mail', 'password' => 'senha']);

        User::create($dados);

        return redirect()->route('usuarios.index')->with('sucesso', 'Usuário cadastrado com sucesso.');
    }
}
