<?php

describe('cns', function () {
    it('retorna erro quando cns é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.cns', null);        
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].cns'))->toContain('Campo CNS obrigatório.');
    });

    it('retorna erro quando cns contém letras', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.cns', 'ABCDaa');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].cns'))->toContain('Valor inválido');
    });


    it('retorna erro quando cns inválido', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.cns', '123456788941');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].cns'))->toContain('Valor inválido');
    });
});