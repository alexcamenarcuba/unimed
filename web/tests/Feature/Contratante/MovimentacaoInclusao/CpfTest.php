<?php

describe('CPF', function () {
    it('retorna erro quando cpf é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.cpf', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].cpf'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando cpf contém letras', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.cpf', 'ABCDEFGHIJK');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].cpf'))->toContain('Valor inválido');
    });

    it('retorna erro quando cpf é inválido', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.cpf', '11111111111');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].cpf'))->toContain('Valor inválido');
    });

    it('retorna erro quando cpf já está integrado no contrato', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.cpf', '560.033.662-49');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].cpf'))->toContain('Já existe um Beneficiário cadastrado no Contrato vinculado a Movimentação Cadastral com o mesmo CPF informado ');
    });
    //** VERIRICAR DEPOIS */
    /*
    it('retorna erro quando cpf está vinculado a movimentação cadastral', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.cpf', '195.623.534-51');
        dd(json_encode($payload));
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].cpf'))->toContain('Já existe um Beneficiário cadastrado na Movimentação Cadastral com o mesmo CPF informado.');
    });
     */
});