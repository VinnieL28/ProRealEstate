<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\Kpi\KpiMetricRecorder;
use Illuminate\Support\Arr;

class TransactionObserver
{
    public function __construct(private readonly KpiMetricRecorder $recorder)
    {
    }

    public function created(Transaction $transaction): void
    {
        $this->recorder->record('transactions.created');
        $this->logContactActivity($transaction, 'Transaction created');
    }

    public function updated(Transaction $transaction): void
    {
        if (!$transaction->wasChanged()) {
            return;
        }

        $changes = Arr::only($transaction->getChanges(), [
            'status',
            'deal_stage',
            'gross_revenue',
            'net_revenue',
        ]);

        $this->logContactActivity($transaction, 'Transaction updated', $changes);

        if ($transaction->wasChanged('status') && $transaction->status === 'closed') {
            $this->recorder->record('transactions.closed');
        }
    }

    protected function logContactActivity(Transaction $transaction, string $message, array $properties = []): void
    {
        if (!$transaction->contact) {
            return;
        }

        activity()
            ->performedOn($transaction->contact)
            ->event('transaction.update')
            ->withProperties($properties)
            ->log($message);
    }
}
