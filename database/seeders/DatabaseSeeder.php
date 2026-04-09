<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Contact;
use App\Models\ColdLead;
use App\Models\SellerLead;
use App\Models\SellerLeadOffer;
use App\Models\Transaction;
use App\Models\CommunicationLog;
use App\Models\KpiMetric;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Database\Seeders\DemoDataSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure a default admin user exists for local development.
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                // The User model uses a "hashed" cast for the password.
                'password' => 'password123',
            ]
        );

        $contacts = Contact::factory()
            ->count(15)
            ->create();

        $contacts->each(function (Contact $contact): void {
            $coldLeads = ColdLead::factory()
                ->count(random_int(0, 2))
                ->for($contact)
                ->create();

            $coldLeads->each(function (ColdLead $lead) use ($contact): void {
                Task::factory()
                    ->for($contact)
                    ->for($lead, 'taskable')
                    ->create([
                        'title' => 'Follow up cold lead',
                        'priority' => 'high',
                    ]);
            });

            $sellerLeads = SellerLead::factory()
                ->count(random_int(1, 3))
                ->for($contact)
                ->create();

            $sellerLeads->each(function (SellerLead $lead) use ($contact): void {
                SellerLeadOffer::factory()
                    ->count(random_int(1, 2))
                    ->for($lead)
                    ->create();

                Task::factory()
                    ->for($contact)
                    ->for($lead, 'taskable')
                    ->create([
                        'title' => 'Review offer with seller',
                        'assigned_to' => 'Leo',
                        'priority' => 'normal',
                    ]);
            });

            Transaction::factory()
                ->count(random_int(0, 2))
                ->for($contact)
                ->create()
                ->each(function (Transaction $transaction) use ($contact): void {
                    CommunicationLog::factory()
                        ->count(random_int(1, 2))
                        ->for($contact)
                        ->state([
                            'loggable_id' => $transaction->id,
                            'loggable_type' => Transaction::class,
                        ])
                        ->create();
                });

            CommunicationLog::factory()
                ->count(random_int(1, 4))
                ->for($contact)
                ->state([
                    'loggable_id' => $contact->id,
                    'loggable_type' => Contact::class,
                ])
                ->create();
        });

        $metrics = [
            'contacts.created',
            'contacts.updated',
            'leads.cold.created',
            'leads.seller.created',
            'leads.cold.drip.paused',
            'transactions.created',
            'transactions.closed',
        ];

        foreach (range(0, 6) as $dayOffset) {
            $date = CarbonImmutable::today()->subDays($dayOffset);

            foreach ($metrics as $metric) {
                KpiMetric::factory()->create([
                    'metric_key' => $metric,
                    'occurred_on' => $date,
                    'metric_value' => random_int(1, 12),
                    'source' => 'seed',
                ]);
            }
        }
    }
}

// Seed demo data for ProEstate CRM
(new DemoDataSeeder())->run();
