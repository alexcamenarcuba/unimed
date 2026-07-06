<?php

describe('tipoDependencia', function () {
    it('retorna erro quando tipoDependencia é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.tipoDependencia', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].tipoDependencia'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando tipoDependencia com numeros', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.tipoDependencia', '123456');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        $erros = implode("\n", $response->json('errors.beneficiarios[0].tipoDependencia'));

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($erros)->toContain('Tipo de Dependência não informado corretamente. normal,estudante,especial/incapaz');
    });

    it('retorna erro quando tipoDependencia invalido', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.tipoDependencia', '111');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        $erros = implode("\n", $response->json('errors.beneficiarios[0].tipoDependencia'));

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($erros)->toContain('Tipo de Dependência não informado corretamente. normal,estudante,especial/incapaz');
    });
});
