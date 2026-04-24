<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Task;
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
        $systemPrompt = $this->buildSystemPrompt();

        $response = $this->client->post('https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'      => 'llama-3.3-70b-versatile',
                'max_tokens' => 1024,
                'messages'   => array_merge(
                    [['role' => 'system', 'content' => $systemPrompt]],
                    $messages
                ),
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['choices'][0]['message']['content'] ?? 'No response received.';
    }

    private function buildSystemPrompt(): string
    {
        $teamId = auth()->user()?->team_id;
        $stats  = $this->fetchCrmStats($teamId);

        return <<<PROMPT
You are an AI assistant built into a real estate CRM called "Pro Real Estate CRM".
You help agents manage leads, deals, properties, and tasks.
Answer questions accurately using the live CRM data provided below.
When asked about counts, values, or names — use the real data. Do not make up information.

=== LIVE CRM DATA (as of right now) ===
LEADS:
  - Total leads: {$stats['leads_total']}
  - New leads: {$stats['leads_new']}
  - Hot leads (score ≥ 70): {$stats['leads_hot']}
  - Leads under contract: {$stats['leads_under_contract']}
  - Leads closed won: {$stats['leads_closed_won']}

DEALS:
  - Total active deals: {$stats['deals_total']}
  - Deals closed this month: {$stats['deals_closed_month']}
  - Total revenue this month: \${$stats['revenue_month']}
  - Deals pending signature: {$stats['deals_pending_esign']}

TASKS:
  - Open tasks: {$stats['tasks_open']}
  - Overdue tasks: {$stats['tasks_overdue']}

PROPERTIES:
  - Total properties: {$stats['properties_total']}

=== END CRM DATA ===

When drafting messages or giving advice, be professional, concise, and actionable.
PROMPT;
    }

    private function fetchCrmStats(?int $teamId): array
    {
        if (!$teamId) {
            return array_fill_keys([
                'leads_total', 'leads_new', 'leads_hot', 'leads_under_contract', 'leads_closed_won',
                'deals_total', 'deals_closed_month', 'revenue_month', 'deals_pending_esign',
                'tasks_open', 'tasks_overdue', 'properties_total',
            ], 0);
        }

        return [
            'leads_total'           => Lead::where('team_id', $teamId)->count(),
            'leads_new'             => Lead::where('team_id', $teamId)->where('stage', 'new_lead')->count(),
            'leads_hot'             => Lead::where('team_id', $teamId)->where('score', '>=', 70)->count(),
            'leads_under_contract'  => Lead::where('team_id', $teamId)->where('stage', 'under_contract')->count(),
            'leads_closed_won'      => Lead::where('team_id', $teamId)->where('stage', 'closed_won')->count(),
            'deals_total'           => Deal::where('team_id', $teamId)->whereNotIn('stage', ['closed_won', 'closed_lost'])->count(),
            'deals_closed_month'    => Deal::where('team_id', $teamId)->where('stage', 'closed_won')->whereMonth('updated_at', now()->month)->count(),
            'revenue_month'         => number_format((float) Deal::where('team_id', $teamId)->where('stage', 'closed_won')->whereMonth('updated_at', now()->month)->sum('profit'), 2),
            'deals_pending_esign'   => Deal::where('team_id', $teamId)->where('esign_status', 'sent')->count(),
            'tasks_open'            => Task::where('team_id', $teamId)->where('status', 'open')->count(),
            'tasks_overdue'         => Task::where('team_id', $teamId)->where('status', 'open')->where('due_date', '<', now())->count(),
            'properties_total'      => Property::where('team_id', $teamId)->count(),
        ];
    }
}
