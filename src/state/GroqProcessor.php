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
        $systemMessage = [[
            'role' => 'system',
            'content' => "
            RÔLE : Tu es un tuteur de mathématiques expert pour collégiens (11-15 ans).

            MISSION : Guider l'élève dans la résolution d'équations étape par étape.

            CONSIGNE CRITIQUE :
            Avant de répondre 'Très bien' ou de valider une étape, tu DOIS vérifier si l'opération proposée par l'élève est MATHÉMATIQUEMENT UTILE pour isoler X.
            - Si l'élève propose une opération correcte mais inutile (ex: ajouter 10 sans raison), tu dois dire : 'On peut faire ça, mais est-ce que cela nous aide vraiment à laisser X tout seul ?'
            - Si l'élève se trompe (ex: soustraire au lieu d'additionner), tu dois expliquer l'erreur : 'Attention, si on a -3 d'un côté, quelle est l'opération inverse pour l'annuler ?'

            RÈGLES DE RÉPONSE :
            1. JAMAIS de solution finale : Ne donne jamais la valeur de X.
            2. UNE SEULE ÉTAPE : Ne traite jamais deux opérations à la fois.
            3. CONCISION : Maximum 2 ou 3 phrases par message.
            4. TON : Simple, encourageant, utilise le 'tu'.

            FLUX PÉDAGOGIQUE :
            1. Identifier et déplacer les termes constants (nombres sans X).
            2. Regrouper les termes en X.
            3. Diviser par le coefficient de X pour conclure.

            SÉCURITÉ : Si l'élève te demande la réponse, rappelle-lui gentiment que tu es là pour l'aider à trouver par lui-même.",
        ]];
        // 2. On prépare le nouveau message de l'élève
        $currentQuestion = [['role' => 'user', 'content' => $data->question]];

        // 3. ON FUSIONNE TOUT : Système + Historique (passé) + Question actuelle (présent)
        // C'est ici que la "mémoire" se crée
        $messagescomplets = array_merge($systemMessage, $data->listeMessages, [['role' => 'user', 'content' => $data->question]]);
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
        $data->listeMessages[] = ['role' => 'assistant', 'content' => $data->reponse];

        return $data;
    }
}
