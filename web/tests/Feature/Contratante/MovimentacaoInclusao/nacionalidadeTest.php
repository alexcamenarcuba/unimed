<?php

describe('nacionalidade', function () {
    it('retorna erro quando nacionalidade é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.nacionalidade', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].nacionalidade'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando nacionalidade com letras', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.nacionalidade', 'aas sas as');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos');

        $errors = $response->json('errors.beneficiarios[0].nacionalidade');

        expect($errors)->toBeArray()
            ->and($errors[0])->toStartWith('Nacionalidade não informado corretamente.');
    });

    it('retorna erro quando inexistente', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.nacionalidade', 'seilaoque');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos');

        $errors = $response->json('errors.beneficiarios[0].nacionalidade');

        expect($errors)->toBeArray()
            ->and($errors[0])->toStartWith('Nacionalidade não informado corretamente.');
    });
});
