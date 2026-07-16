<?php

describe('generoSocial', function () {

    it('retorna erro quando generoSocial contém letras', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.generoSocial', 'ABCDaa');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].generoSocial'))->toContain('Gênero Social não informado corretamente. 2,1,99,4,3');
    });

    it('retorna erro quando generoSocial invalido', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.generoSocial', '15');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].generoSocial'))->toContain('Gênero Social não informado corretamente. 2,1,99,4,3');
    });
});
