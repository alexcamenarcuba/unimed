<?php

use Faker\Factory as FakerFactory;

if (! function_exists('fakerPtBr')) {
    function fakerPtBr(): \Faker\Generator
    {
        static $faker;

        return $faker ??= FakerFactory::create('pt_BR');
    }
}

function cpfValidoUnico(): string
{
    return \Faker\Factory::create('pt_BR')->cpf(false);
}

function gerarCNS(): string
{
    do {
        $n1  = rand(0, 9);
        $n2  = rand(0, 9);
        $n3  = rand(0, 9);
        $n4  = rand(0, 9);
        $n5  = rand(0, 9);
        $n6  = rand(0, 9);
        $n7  = rand(0, 9);
        $n8  = rand(0, 9);
        $n9  = rand(0, 9);
        $n10 = rand(0, 9);
        $n11 = rand(0, 9);

        $pis = '' . $n1 . $n2 . $n3 . $n4 . $n5 . $n6 . $n7 . $n8 . $n9 . $n10 . $n11;

        $soma = 0;
        foreach (str_split($pis) as $indice => $numero) {
            $soma += $numero * (15 - $indice);
        }

        $resto = fmod($soma, 11);
        $dv    = 11 - $resto;

        if ($dv == 11) {
            $dv = 0;
        }
    } while ($dv == 0);

    if ($dv == 10) {
        $soma = 0;
        foreach (str_split($pis) as $indice => $numero) {
            $soma += $numero * (15 - $indice);
        }
        $soma += 2;

        $resto = fmod($soma, 11);
        $dv    = 11 - $resto;

        $resultado = $pis . '001' . $dv;
    } else {
        $resultado = $pis . '000' . $dv;
    }

    return $resultado;
}

function nomeComTamanho(int $tamanho): string
{
    $nome = '';

    while (mb_strlen($nome) < $tamanho) {
        $nome .= fakerPtBr()->firstName() . ' ';
    }

    return trim(mb_substr($nome, 0, $tamanho));
}
