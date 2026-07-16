<?php

describe('inicioVigencia', function () {
    it('retorna erro quando inicioVigencia é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.inicioVigencia', null);        
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].inicioVigencia'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando inicioVigencia contém numeros', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.inicioVigencia', '123456 456 123');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].inicioVigencia'))->toContain('Início vigência não informado corretamente. em_24_horas,1_dia_mes_seguinte,ultimo_dia_mes,especificar_data');
    });

    it('retorna erro quando diferente das opções validas', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.inicioVigencia', 'seilaoque');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].inicioVigencia'))->toContain('Início vigência não informado corretamente. em_24_horas,1_dia_mes_seguinte,ultimo_dia_mes,especificar_data');
    });

});