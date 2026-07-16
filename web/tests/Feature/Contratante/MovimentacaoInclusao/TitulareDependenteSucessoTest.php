<?php

use Illuminate\Support\Facades\Http;

it('cria movimentacao de inclusao com sucesso', function () {
    $token    = loginContratanteToken();
    $response = movimentacaoInclusao($token, TitularDependente());
    //$response->dump();
    expect($response->status())->toBe(201)
        ->and($response->json('success'))->toBeTrue()
        ->and($response->json('message'))->toBe('Movimentação criada com sucesso')
        ->and($response->json('data.movimentacao.id'))->not->toBeEmpty()
        ->and($response->json('data.movimentacao.protocolo'))->not->toBeEmpty()
        ->and($response->json('data.beneficiarios'))->toHaveCount(2)
        ->and($response->json('data.beneficiarios.0.cpf'))->not->toBeEmpty();
});
