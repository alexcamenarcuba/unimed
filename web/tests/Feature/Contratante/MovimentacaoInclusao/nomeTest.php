<?php

describe('nome', function () {
    it('retorna erro quando nome é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.nome', null);        
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].nome'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando nome contém numeros', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.nome', 'ABCDaa 122EF GHIJK');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].nome'))->toContain('Erro ao validar o nome do beneficiário: Nomes que contenham um ou mais números: 1, 2, 3, 4, 5, 6, 7, 8, 9 e 0.');
    });

    it('retorna erro quando nome com mais de 255 caracteres - caracter especial - uma letra', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.nome', nomeComTamanho(256));

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].nome'))->toContain('Máximo de 255 caracteres');
    });
});