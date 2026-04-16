<?php

namespace App\Services;

use GuzzleHttp\Client;

class AIAssistantService
{
    private Client $client;
    private string $apiKey;

    public function __construct()
    {
        $this->client = new Client(['verify' => false]);
        $this->apiKey = env('GROQ_API_KEY', '');
    }

    public function chat(array $messages): string
    {
        $response = $this->client->post('https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'      => 'llama-3.3-70b-versatile',
                'max_tokens' => 1024,
                'messages'   => array_merge(
                    [['role' => 'system', 'content' => 'You are an AI assistant built into a real estate CRM. You help agents manage leads, deals, properties, and tasks. You can answer questions, help draft messages, and give advice based on CRM context.']],
                    $messages
                ),
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['choices'][0]['message']['content'] ?? 'No response received.';
    }
}
