<?php

describe('estadoCivil', function () {
    it('retorna erro quando estadoCivil é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.estadoCivil', null);        
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].estadoCivil'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando estadoCivil contém numeros', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.estadoCivil', '123456');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].estadoCivil'))->toContain('Estado Civil não informado corretamente. solteiro,casado,divorciado,viuvo,separado,companheiro');
    });

    it('retorna erro quando estadoCivil inexistente', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.estadoCivil', 'MF');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].estadoCivil'))->toContain('Estado Civil não informado corretamente. solteiro,casado,divorciado,viuvo,separado,companheiro');
    });
});