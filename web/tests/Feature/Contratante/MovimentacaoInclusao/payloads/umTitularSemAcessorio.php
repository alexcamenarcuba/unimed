<?php 

return [
    'nroContrato' => '37451-0-297',
    'beneficiarios' => [
        [
            'cpf'                => cpfValidoUnico(),
            'nome'               => nome(),
            'dataNascimento'     => date('Y-m-d', strtotime('-30 years')),
            'sexo'               => 'm',
            'cns'                => gerarCNS(),
            'grauDependencia'    => '00',
            'tipoDependencia'    => 'normal',
            'cpfTitular'         => null,
            'dataVinculoTitular' => null,
            'inicioVigencia'     => 'especificar_data',
            'dataInicioVigencia' => date('Y-m-d', strtotime('+30 day')),
            'dataAdmissao'       => date('Y-m-d', strtotime('-10 days')),
            'numeroMatricula'    => null,
            'nomeMae'            => 'mae '.nome(),
            'nomePai'            => 'pai '.nome(),
            'estadoCivil'        => 'casado',
            'nomeSocial'         => '',
            'generoSocial'       => '',
            'paisNascimento'     => 32,
            'nacionalidade'      => 'Brasileira',
            'naturalidadeCidade' => 'Curitiba',
            'naturalidadeUF'     => 'PR',
            'raca'               => '1',
            'profissaoCBO'       => '',
            'emails'    => [['email' => 'zemira@email.com']],
            'enderecos' => [[
                'tipo'            => 'residencial',
                'cep'             => '80410-210',
                'correspondencia' => false,
                'faturamento'     => true,
                'notaFiscal'      => true,
                'cartaoMagnetico' => false,
                'uf'              => 'pr',
                'cidade'          => 'curitiba',
                'endereco'        => 'alameda cabral',
                'bairro'          => 'centro',
                'numero'          => '471',
                'complemento'     => 'Apto 77',
            ]],
            'telefones' => [[
                'tipo'   => 'celular',
                'classe' => 'residencial',
                'ddi'    => '',
                'ddd'    => '11',
                'numero' => '912345678',
            ]],
            'produtos' => [
                ['codigo' => '1947', 'tipo' => 'assistencial'],
                ['codigo' => '5042', 'tipo' => 'acessorio'],
            ]            
        ]
    ],
];