<?php

describe('grauDependencia', function () {
    it('retorna erro quando grauDependencia é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.grauDependencia', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].grauDependencia'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando grauDependencia contém letras', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.grauDependencia', 'ABCDaa');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        $erros = implode("\n", $response->json('errors.beneficiarios[0].grauDependencia'));

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($erros)->toContain('Grau de Dependência não informado corretamente.');
    });

    it('retorna erro quando grauDependencia invalido', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.grauDependencia', '111');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        $erros = implode("\n", $response->json('errors.beneficiarios[0].grauDependencia'));

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($erros)->toContain('Grau de Dependência não informado corretamente.');
    });

    it('retorna erro quando grauDependencia nao permitido', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.grauDependencia', 'x');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        $erros = implode("\n", $response->json('errors.beneficiarios[0].grauDependencia'));

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($erros)->toContain('Grau de Dependência não informado corretamente.');
    });
});
