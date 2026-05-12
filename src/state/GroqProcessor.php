<?php

declare(strict_types=1);

namespace App\state;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\GroqPrompt;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * GroqProcessor gère l'envoi des prompts à l'API Groq et la réception des réponses.
 * Il implémente ProcessorInterface pour s'intégrer au flux d'Api Platform.
 */
class GroqProcessor implements ProcessorInterface
{
    private HttpClientInterface $client;

    // Tableau contenant toutes les clés API disponibles pour l'équilibrage de charge
    private array $apiKeys;

    /**
     * Le constructeur reçoit le client HTTP et les 5 clés API configurées dans services.yaml.
     */
    public function __construct(
        HttpClientInterface $client,
        string $groqApiKey,
        string $groqApiKey2,
        string $groqApiKey3,
        string $groqApiKey4,
        string $groqApiKey5,
    ) {
        $this->client = $client;
        // On regroupe les clés dans un tableau pour pouvoir piocher dedans aléatoirement
        $this->apiKeys = [
            $groqApiKey,
            $groqApiKey2,
            $groqApiKey3,
            $groqApiKey4,
            $groqApiKey5,
        ];
    }

    /**
     * Sélectionne une clé API au hasard dans le tableau.
     * Cela permet de répartir les requêtes sur plusieurs comptes Groq (Load Balancing).
     */
    public function getRandomApiKey(): string
    {
        // array_rand retourne un index au hasard (0, 1, 2, 3 ou 4)
        $key = $this->apiKeys[array_rand($this->apiKeys)];

        // Sécurité : si la clé choisie est vide, on retourne la première du tableau
        return $key ?: $this->apiKeys[0];
    }

    /**
     * Méthode principale de traitement de la requête.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // Vérification de sécurité : on s'assure que l'objet reçu est bien un GroqPrompt
        if (!$data instanceof GroqPrompt) {
            return $data;
        }

        // 1. Définition du message système (Le "cerveau" pédagogique du tuteur)
        $systemMessage = [[
            'role' => 'system',
            'content' => "
            RÔLE : Tu es un tuteur de mathématiques expert pour collégiens (11-15 ans).
            MISSION : Guider l'élève dans la résolution d'équations étape par étape.

            CONSIGNE CRITIQUE :
            Vérifier si l'opération proposée est MATHÉMATIQUEMENT UTILE pour isoler X.

            RÈGLES DE RÉPONSE :
            1. JAMAIS de solution finale.
            2. UNE SEULE ÉTAPE à la fois.
            3. CONCISION (2-3 phrases).
            4. TON simple et encourageant.",
        ]];

        // 2. Fusion des messages pour créer le contexte (Mémoire de la conversation)
        // array_merge combine : Message Système + Historique existant + Question actuelle
        $messagescomplets = array_merge(
            $systemMessage,
            $data->listeMessages,
            [['role' => 'user', 'content' => $data->question]]
        );

        // 3. Préparation du corps de la requête pour l'API Groq
        $body = [
            'model' => 'llama-3.1-8b-instant',
            'messages' => $messagescomplets,
        ];

        // 4. Choix de la clé API pour cette requête spécifique
        $randomKey = $this->getRandomApiKey();

        // 5. Envoi de la requête POST à Groq
        $response = $this->client->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.$randomKey, // Utilisation de la clé aléatoire
                'Content-Type' => 'application/json',
            ],
            'json' => $body,
        ]);

        // 6. Transformation de la réponse JSON en tableau PHP
        $result = $response->toArray();

        // On stocke la réponse de l'IA dans l'objet GroqPrompt
        $data->reponse = $result['choices'][0]['message']['content'];

        // 7. Mise à jour de l'historique pour les prochains échanges
        // On enregistre ce que l'élève a dit et ce que l'IA a répondu
        $data->listeMessages[] = ['role' => 'user', 'content' => $data->question];
        $data->listeMessages[] = ['role' => 'assistant', 'content' => $data->reponse];

        // On retourne l'objet mis à jour vers l'API
        return $data;
    }
}
