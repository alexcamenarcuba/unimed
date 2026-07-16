<?php

describe('nomeSocial', function () {
    
    it('retorna erro quando nomeSocial contém numeros', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.nomeSocial', 'ABCDaa 122EF GHIJK');        
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].nomeSocial'))->toContain('Erro ao validar o Nome Social do beneficiário: Nomes que contenham um ou mais números: 1, 2, 3, 4, 5, 6, 7, 8, 9 e 0.');
    });

    it('retorna erro quando nomeSocial com mais de 255 caracteres - caracter especial - uma letra', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.nomeSocial', nomeComTamanho(256));
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].nomeSocial'))->toContain('Nome Social excede a quantidade de caracteres permitidos.');
    });
});