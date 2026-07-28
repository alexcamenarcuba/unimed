<?php

describe('produto', function () {
    it('retorna erro quando produto é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.produtos', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].produtos'))
            ->toContain('Campo obrigatório');
    });

    it('retorna erro quando produto codigo null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.produtos.0.codigo', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].produtos[0].codigo'))
            ->toContain('Campo obrigatório');
    });

    it('retorna erro quando produto codigo invalido', function () {
        $payload = payloadInclusaoValido();
        $produtoInvalido = 154;
        data_set($payload, 'beneficiarios.0.produtos.0.codigo', $produtoInvalido);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].produtos[0].codigo'))
            ->toContain("Produto assistencial com código $produtoInvalido não encontrado no contrato.");
    });
});
