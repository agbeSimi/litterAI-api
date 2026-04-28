<?php

declare(strict_types=1);

namespace App\state;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\GroqPrompt;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GroqProcessor implements ProcessorInterface
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $groqApiKey)
    {
        $this->client = $client;
        $this->apiKey = $groqApiKey;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // On vérifie que c'est le bon objet, sinon on quitte la fonction
        if (!$data instanceof GroqPrompt) {
            return $data;
        }

        // À partir d'ici, on sait que $data est un GroqPrompt
        // On prépare les données pour Groq
        $body = [
            'model' => 'llama-3.1-8b-instant',
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un tuteur de maths.'],
                ['role' => 'user', 'content' => $data->question],
            ],
        ];

        // On lance l'appel
        $response = $this->client->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $body,
        ]);

        // On récupère la réponse
        $result = $response->toArray();
        // IMPORTANT : Vérifie que ta propriété dans GroqPrompt
        // s'appelle bien "reponse" et non "response" (orthographe)
        $data->reponse = $result['choices'][0]['message']['content'];

        return $data;
    }
}
