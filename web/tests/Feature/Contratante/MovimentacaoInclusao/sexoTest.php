<?php

describe('sexo', function () {
    it('retorna erro quando sexo é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.sexo', null);        
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].sexo'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando sexo contém numeros', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.sexo', 'ABCDaa 122EF GHIJK');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].sexo'))->toContain('Sexo não informado corretamente. M,F');
    });

    it('retorna erro quando sexo com mais de 2 caracteres - caracter especial - uma letra', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.sexo', sexoComTamanho(2));

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].sexo'))->toContain('Sexo não informado corretamente. M,F');
    });

    it('retorna erro quando sexo diferente de m ou f', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.sexo', 'x');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].sexo'))->toContain('Sexo não informado corretamente. M,F');
    });
});