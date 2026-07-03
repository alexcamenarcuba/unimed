<?php
use Illuminate\Support\Facades\Http;

function loginContratanteToken(): string
{
    static $cachedToken = null;

    if (is_string($cachedToken) && $cachedToken !== '') {
        return $cachedToken;
    }

    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'crm-qa'       => true,
    ])->post('http://crm/api/v1/contratante/login', [
        'nomeUsuario' => 'acubaapi',
        'senha'       => 'XzP6#F%W',
    ]);

    $token = $response->json('data.token');

    if (!is_string($token) || $token === '') {
        throw new \RuntimeException(sprintf(
            'Falha ao obter token de contratante. HTTP %s. Body: %s',
            $response->status(),
            $response->body()
        ));
    }

    $cachedToken = $token;

    return $cachedToken;
}

function contratanteHttp(string $token)
{
    return Http::withHeaders([
        'Content-Type'  => 'application/json',
        'Authorization' => 'Bearer ' . $token,
        'crm-qa'        => 'true',
    ]);
}

function payloadInclusaoValido(): array
{
    return require __DIR__ . '/MovimentacaoInclusao/payloads/inclusao_valido.php';
}

function movimentacaoInclusao(string $token, array $payload)
{
    return contratanteHttp($token)
        ->post('http://crm/api/v1/contratante/movimentacaoInclusao', $payload);
}