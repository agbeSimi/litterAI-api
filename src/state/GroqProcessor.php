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

        // 1. On définit le rôle du prof (le message système)
        $systemMessage = [['role' => 'system', 'content' => 'Tu es un tuteur de maths pour collegiens qui parle de manière très simple et concis pour que les élèves te comprenne.']];

        // 2. On prépare le nouveau message de l'élève
        $currentQuestion = [['role' => 'user', 'content' => $data->question]];

        // 3. ON FUSIONNE TOUT : Système + Historique (passé) + Question actuelle (présent)
        // C'est ici que la "mémoire" se crée
        $messagescomplets = array_merge($systemMessage, $data->listeMessages, $currentQuestion);

        // À partir d'ici, on sait que $data est un GroqPrompt
        // On prépare les données pour Groq
        $body = [
            'model' => 'llama-3.1-8b-instant',
            'messages' => $messagescomplets,
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
        $data->reponse = $result['choices'][0]['message']['content'];

        // --- AJOUTE CES LIGNES ICI ---
        // On ajoute la question actuelle à l'historique
        $data->listeMessages[] = ['role' => 'user', 'content' => $data->question];
        // On ajoute la réponse de l'IA à l'historique
        $data->listeMessages[] = ['role' => 'assistant', 'content' => $data->reponse];

        return $data;
    }
}
