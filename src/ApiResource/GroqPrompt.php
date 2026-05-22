<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\state\GroqProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: 'api/ia/groq',
            processor: GroqProcessor::class
        ),
    ])]
class GroqPrompt
{
    public string $question;
    public string $reponse;
    public array $listeMessages;
}
