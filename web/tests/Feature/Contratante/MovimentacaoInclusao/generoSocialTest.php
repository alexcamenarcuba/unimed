<?php

describe('generoSocial', function () {

    it('retorna erro quando generoSocial contém letras', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.generoSocial', 'ABCDaa');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos');

        $errors = $response->json('errors.beneficiarios[0].generoSocial');

        expect($errors)->toBeArray()
            ->and($errors[0])->toContain('Gênero Social não informado corretamente');
    });

    it('retorna erro quando generoSocial invalido', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.generoSocial', '15');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos');

        $errors = $response->json('errors.beneficiarios[0].generoSocial');

        expect($errors)->toBeArray()
            ->and($errors[0])->toContain('Gênero Social não informado corretamente');
    });
});
