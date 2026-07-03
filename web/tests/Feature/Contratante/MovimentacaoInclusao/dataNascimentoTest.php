<?php

describe('dataNascimento', function () {
    it('retorna erro quando data de nascimento é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataNascimento', null);

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataNascimento'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando data de nascimento contém letras', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataNascimento', 'ABCDEFGHIJK');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataNascimento'))->toContain('Valor inválido');
    });

    it('retorna erro quando data de nascimento é inválida', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataNascimento', '31/02/2020');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataNascimento'))->toContain('Valor inválido');
    });

    it('retorna erro quando data de nascimento com dia e mês inválidos', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataNascimento', '31/13/2020');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataNascimento'))->toContain('Valor inválido');
    });

    it('retorna erro quando com data futura', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataNascimento', '31/12/2099');
        
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataNascimento'))->toContain('A data de nascimento não pode ser posterior a data atual.');
    });
});