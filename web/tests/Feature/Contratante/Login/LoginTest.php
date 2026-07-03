<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

function loginContratante(string $usuario, string $senha)
{
    return Http::withHeaders([
        'Content-Type' => 'application/json',
        'crm-qa'       => 'true',
    ])->post('http://crm/api/v1/contratante/login', [
        'nomeUsuario' => $usuario,
        'senha'       => $senha,
    ]);
}

it('realiza login com sucesso e retorna token', function () {
    $response = loginContratante('acubaapi', 'XzP6#F%W');

    expect($response->status())->toBe(200)
        ->and($response->json('success'))->toBeTrue()
        ->and($response->json('message'))->toBe('Token gerado com sucesso')
        ->and($response->json('data.token'))->not->toBeEmpty()
        ->and($response->json('data.token_type'))->toBe('bearer')
        ->and($response->json('data.expires_in'))->toBeInt();
});

it('retorna erro ao tentar login com senha incorreta', function () {
    $response = loginContratante('acubaapi', 'senha_errada');

    expect($response->status())->toBe(401)
        ->and($response->json('success'))->toBeFalse()
        ->and($response->json('errors'))->toContain('Usuário ou senha inválidos');
});