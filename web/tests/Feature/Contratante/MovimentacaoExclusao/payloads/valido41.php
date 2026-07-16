<?php 

return [
    'nroContrato' => '37451-0-297',
    'exclusaoDesistenciaPlano' => false,
    'beneficiarios' => [
        [
            "cpf"           => "003.406.669-19",
            "tipo"          => "titular",            
            "motivo"        => "41",
            "submotivo"     => "PEA",
            "devolveuCartao"=> false,
            "complemento"   => "complemento Máximo de 100 caracteres",
            "email"         => "EXCLUSAO@MAILINATOR.COM",
            'telefones' => [[
                'tipo'   => 'celular',
                'classe' => 'residencial',
                'ddd'    => '11',
                'numero' => '912345678',
            ]],
            "dataExclusao" => null
        ],
    ],
];