<?php
use Carbon\Carbon;

describe('dataAdmissao', function () {
    it('retorna erro quando dataAdmissao é null', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataAdmissao', null);
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataAdmissao'))->toContain('Campo obrigatório');
    });

    it('retorna erro quando dataAdmissao contém letras', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataAdmissao', 'aa aaaa aaa');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataAdmissao'))->toContain('Campo Data Admissão Empresa  obrigatório.');
    });

    it('retorna erro quando dataAdmissao contém numeros', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataAdmissao', '123456 456 123');        
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataAdmissao'))->toContain('Campo Data Admissão Empresa  obrigatório.');
    });

    it('retorna erro quando com dia e mes inválido', function () {
        $payload = payloadInclusaoValido();
        data_set($payload, 'beneficiarios.0.dataAdmissao', '2026-02-31');
        $response = movimentacaoInclusao(loginContratanteToken(), $payload);
        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataAdmissao'))->toContain('Campo Data Admissão Empresa  obrigatório.');
    });

    it('retorna erro quando dataAdmissao é uma data futura', function () {
        $payload = payloadInclusaoValido();

        $dataAdmissao = date('Y-m-d', strtotime('+10 day'));
        data_set($payload, 'beneficiarios.0.dataAdmissao', $dataAdmissao);

        // pegue a data de vigência do próprio payload (a que já está lá)
        $dataInicioVigencia = data_get($payload, 'beneficiarios.0.dataInicioVigencia');

        $response = movimentacaoInclusao(loginContratanteToken(), $payload);

        $dataAdmissaoFormatada = Carbon::parse($dataAdmissao)->format('d/m/Y');
        $dataVigenciaFormatada = Carbon::parse($dataInicioVigencia)->format('d/m/Y');

        expect($response->status())->toBe(400)
            ->and($response->json('success'))->toBeFalse()
            ->and($response->json('message'))->toBe('Dados inválidos')
            ->and($response->json('errors.beneficiarios[0].dataAdmissao.0'))
            ->toBe("A data de admissão ({$dataAdmissaoFormatada}) deve ser inferior ou igual a Data Início Vigência do beneficiário ({$dataVigenciaFormatada}).");
    });
});
