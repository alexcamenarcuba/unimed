<?php

describe('dataInicioVigencia', function () {
    it('retorna erro quando dataInicioVigencia é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataInicioVigencia', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataInicioVigencia'))->toContain('Campo Data Início Vigência obrigatório.');
    });

    it('retorna erro quando dataInicioVigencia contém numeros', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataInicioVigencia', '123456 456 123');        
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataInicioVigencia'))->toContain('Data Inicio Vigência inválido.');
    });

    it('retorna erro quando data invalida', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataInicioVigencia', '31/02/2024');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataInicioVigencia'))->toContain('Data Inicio Vigência inválido.');
    });

    it('retorna erro quando data retroativa', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataInicioVigencia', '2026-01-01');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos');

        $errors = $response->json('errors.beneficiarios[0].dataInicioVigencia');

        expect($errors)->toBeArray()
            ->and($errors[0])->toContain('não pode ser menor que a data atual');
    });
});
