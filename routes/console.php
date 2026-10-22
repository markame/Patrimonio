<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('escola:administrador {email?} {--nome=Administrador}', function () {
    if (User::exists()) {
        $this->error('A instalação já possui usuários. Novos usuários devem ser cadastrados pelo administrador no sistema.');

        return 1;
    }
    $email = $this->argument('email') ?: $this->ask('E-mail do administrador');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Informe um e-mail válido.');

        return 1;
    }
    if (User::where('email', $email)->exists()) {
        $this->error('Este e-mail já está cadastrado.');

        return 1;
    }
    $senha = $this->secret('Senha (mínimo de 10 caracteres)');
    if (mb_strlen($senha ?? '') < 10) {
        $this->error('A senha deve conter pelo menos 10 caracteres.');

        return 1;
    }
    $usuario = new User(['name' => $this->option('nome'), 'email' => $email, 'password' => Hash::make($senha)]);
    $usuario->administrador = true;
    $usuario->save();
    $this->info('Administrador criado com sucesso.');
})->purpose('Criar acesso administrativo para a escola');
