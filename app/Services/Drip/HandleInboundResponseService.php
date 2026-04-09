<?php

namespace App\Services\Drip;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HandleInboundResponseService
{
    public function pauseAndAssign(Model $lead, string $message, string $channel = 'manual'): void
    {
        DB::transaction(function () use ($lead, $message, $channel): void {
            $contact = $this->resolveContact($lead);

            if (method_exists($lead, 'setAttribute')) {
                $lead->drip_status = 'paused';
                $lead->save();
            }

            $taskPayload = [
                'title' => 'Inbound Response',
                'description' => "Respond via {$channel}: {$message}",
                'assigned_to' => 'Leo',
                'priority' => 'high',
            ];

            if ($contact) {
                $taskPayload['contact_id'] = $contact->id;
            }

            $lead->tasks()->create($taskPayload);

            if ($contact) {
                $notes = trim((string) $contact->notes);
                $contact->notes = trim($message . "\n\n" . $notes);
                $contact->save();
            }
        });
    }

    protected function resolveContact(Model $lead): ?Contact
    {
        if (method_exists($lead, 'contact')) {
            return $lead->contact;
        }

        return null;
    }
}
