<?php

namespace App\Livewire;

use App\Services\AIAssistantService;
use Livewire\Component;

class AiChat extends Component
{
    public array $messages = [];
    public string $input = '';
    public bool $loading = false;

    public function sendMessage(): void
    {
        $text = trim($this->input);
        if ($text === '') {
            return;
        }

        $this->messages[] = ['role' => 'user', 'content' => $text];
        $this->input = '';
        $this->loading = true;

        $history = array_map(fn($m) => ['role' => $m['role'], 'content' => $m['content']], $this->messages);

        try {
            $reply = app(AIAssistantService::class)->chat($history);
        } catch (\Throwable $e) {
            $reply = 'Error: ' . $e->getMessage();
        }

        $this->messages[] = ['role' => 'assistant', 'content' => $reply];
        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.ai-chat');
    }
}
