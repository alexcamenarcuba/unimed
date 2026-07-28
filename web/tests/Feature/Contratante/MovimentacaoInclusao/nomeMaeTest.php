<?php

describe('nomeMae', function () {
    it('retorna erro quando nomeMae é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.nomeMae', null);        
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].nomeMae'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando nomeMae com mais de 255 caracteres - caracter especial - uma letra', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.nomeMae', nomeComTamanho(256));
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].nomeMae'))->toContain('Máximo de 255 caracteres');
    });
});