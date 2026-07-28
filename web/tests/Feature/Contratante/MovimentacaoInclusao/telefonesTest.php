<?php

describe('telefone', function () {
   /*
    it('retorna erro quando telefone é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.telefones', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(201)
            ->and($response->json('success'))->toBeTrue()
            ->and($response->json('message'))->toBe('Movimentação criada com sucesso')
            ->and($response->json('data.movimentacao.id'))->not->toBeEmpty()
            ->and($response->json('data.movimentacao.protocolo'))->not->toBeEmpty()
            ->and($response->json('data.beneficiarios'))->toHaveCount(1)
            ->and($response->json('data.beneficiarios.0.cpf'))->not->toBeEmpty();
    });
    */
    it('retorna erro quando telefone enviando incompleto', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.telefones.0.tipo', 'celular');
        data_set($payload, 'beneficiarios.0.telefones.0.classe', null);
        data_set($payload, 'beneficiarios.0.telefones.0.numero', null);
        data_set($payload, 'beneficiarios.0.telefones.0.ddd', null);
        data_set($payload, 'beneficiarios.0.telefones.0.ddi', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].telefones[0].classe'))
            ->toContain('Campo obrigatório');
    });

    it('retorna erro quando telefone enviando tipo invalido', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.telefones.0.tipo', 'celulara');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos');

        $errors = $response->json('errors.beneficiarios[0].telefones[0].tipo');

        expect($errors)->toBeArray()
            ->and($errors[0])->toContain('Valor inválido');
    });

    it('retorna erro quando telefone ddi tamanho maximo', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.telefones.0.ddi', 51547);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].telefones[0].ddi'))
            ->toContain('Máximo de 3 caracteres');
    });

    it('retorna erro quando telefone ddi string', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.telefones.0.ddi', 'abc');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].telefones[0].ddi'))
            ->toContain('Valor deve ser numérico');
    });

    it('retorna erro quando telefone ddd tamanho maximo', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.telefones.0.ddd', 51547);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].telefones[0].ddd'))
            ->toContain('Máximo de 2 caracteres');
    });

    it('retorna erro quando telefone ddd string', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.telefones.0.ddd', 'abc');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].telefones[0].ddd'))
            ->toContain('Valor deve ser numérico');
    });

     it('retorna erro quando telefone classe invalida', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.telefones.0.classe', 'abc');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos');

        $errors = $response->json('errors.beneficiarios[0].telefones[0].classe');

        expect($errors)->toBeArray()
            ->and($errors[0])->toContain('Valor inválido');
    });

    it('retorna erro quando telefone numero tamanho maximo', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.telefones.0.numero', '01234567890');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].telefones[0].numero'))
            ->toContain('Máximo de 9 caracteres');
    });

    it('retorna erro quando telefone numero tamanho minimo', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.telefones.0.numero', '0123456');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].telefones[0].numero'))
            ->toContain('Mínimo de 8 caracteres');
    });
    
});
