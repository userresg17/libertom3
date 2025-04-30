<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response; // Para opcionalmente carregar a resposta da API

class ExternalApiException extends Exception
{
    public ?Response $response; // Resposta HTTP original, se disponível

    /**
     * Cria uma nova instância da exceção.
     *
     * @param string $message Mensagem de erro
     * @param int $code Código de erro (geralmente HTTP status code)
     * @param \Throwable|null $previous Exceção anterior
     * @param \Illuminate\Http\Client\Response|null $response Resposta HTTP que causou o erro
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, ?Response $response = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /**
     * Retorna a resposta HTTP original, se houver.
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Retorna o corpo da resposta como array, se disponível.
     */
    public function getResponseBody(): ?array
    {
        return $this->response?->json();
    }
}