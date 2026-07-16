<?php

describe('paisNascimento', function () {
    it('retorna erro quando paisNascimento é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.paisNascimento', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].paisNascimento'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando paisNascimento com letras', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.paisNascimento', 'aas sas as');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos');

        $errors = $response->json('errors.beneficiarios[0].paisNascimento');

        expect($errors)->toBeArray()
            ->and($errors[0])->toStartWith('País de Nascimento não informado corretamente.');
    });

    it('retorna erro quando inexistente', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.paisNascimento', 'seilaoque');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos');

        $errors = $response->json('errors.beneficiarios[0].paisNascimento');

        expect($errors)->toBeArray()
            ->and($errors[0])->toStartWith('País de Nascimento não informado corretamente.');
    });
});
