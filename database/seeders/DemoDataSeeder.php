<?php

namespace Database\Seeders;

use App\Models\CallLog;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create a demo team and users
        // First create the owner user
        $owner = User::firstOrCreate(
            ['email' => 'owner@proestate.test'],
            [
                'name' => 'Owner ProEstate',
                'password' => Hash::make('password'),
                'role' => 'owner',
            ]
        );

        // Then create the team with the owner
        $team = Team::firstOrCreate(
            ['name' => 'ProEstate Demo'],
            ['owner_id' => $owner->id, 'default_currency' => 'USD']
        );

        // Update owner's team_id
        $owner->team_id = $team->id;
        $owner->save();

        $users = collect([
            ['name' => 'Admin One', 'email' => 'admin@proestate.test', 'role' => 'admin'],
            ['name' => 'Acquisition Amy', 'email' => 'acq@proestate.test', 'role' => 'acquisition_manager'],
            ['name' => 'Lead Leo', 'email' => 'lead@proestate.test', 'role' => 'lead_manager'],
            ['name' => 'Caller Cara', 'email' => 'caller@proestate.test', 'role' => 'cold_caller'],
            ['name' => 'Dispo Dan', 'email' => 'dispo@proestate.test', 'role' => 'dispo_manager'],
        ])->map(function ($u) use ($team) {
            return User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'role' => $u['role'],
                    'team_id' => $team->id,
                ]
            );
        });

        Setting::firstOrCreate(
            ['team_id' => $team->id],
            [
                'default_currency' => 'USD',
                'pipeline_stages' => [
                    ['key' => 'new_lead', 'label' => 'New Lead'],
                    ['key' => 'no_contact', 'label' => 'No Contact Made'],
                    ['key' => 'contact_made', 'label' => 'Contact Made'],
                    ['key' => 'appointment_set', 'label' => 'Appointments Set'],
                    ['key' => 'due_diligence', 'label' => 'Due Diligence'],
                    ['key' => 'offer_made', 'label' => 'Offers Made'],
                    ['key' => 'under_contract', 'label' => 'Under Contract'],
                    ['key' => 'closed_won', 'label' => 'Closed Won'],
                    ['key' => 'closed_lost', 'label' => 'Closed Lost'],
                ],
                'lead_sources' => ['Cold Call', 'SMS', 'PPC', 'Referral', 'Agent', 'D4D', 'Other'],
                'team_phone_numbers' => ['+1-555-1000', '+1-555-2000'],
            ]
        );

        // Seed leads, properties, deals
        $stages = ['new_lead', 'contact_made', 'appointment_set', 'due_diligence', 'offer_made', 'under_contract', 'closed_won', 'closed_lost'];
        $sources = ['Cold Call', 'SMS', 'PPC', 'Referral', 'Agent', 'D4D', 'Other'];

        $leads = collect(range(1, 12))->map(function ($i) use ($team, $stages, $sources, $users) {
            return Lead::create([
                'team_id' => $team->id,
                'owner_name' => "Lead #$i",
                'phone' => "+1-555-10{$i}0",
                'email' => "lead{$i}@demo.test",
                'lead_source' => Arr::random($sources),
                'motivation_level' => rand(1, 5),
                'asking_price' => rand(150000, 400000),
                'max_offer' => rand(120000, 350000),
                'last_offer' => rand(100000, 300000),
                'stage' => Arr::random($stages),
                'assigned_to_id' => $users->random()->id,
                'notes' => 'Demo seeded lead',
            ]);
        });

        $properties = $leads->map(function (Lead $lead, $i) use ($team) {
            return Property::create([
                'team_id' => $team->id,
                'lead_id' => $lead->id,
                'address' => "{$i}00 Demo St",
                'city' => 'Demo City',
                'state' => 'CA',
                'zip' => '90000',
                'type' => 'sfr',
                'beds' => rand(2, 4),
                'baths' => rand(1, 3),
                'sqft' => rand(1200, 2400),
                'year_built' => rand(1975, 2015),
                'arv' => rand(300000, 550000),
                'estimated_repairs' => rand(10000, 45000),
                'estimated_rent' => rand(1800, 3200),
                'acquisition_price' => rand(180000, 350000),
                'status' => Arr::random(['prospect', 'under_contract', 'owned', 'listed']),
                'notes' => 'Demo property',
            ]);
        });

        $deals = $properties->take(6)->map(function (Property $property, $idx) use ($team, $leads) {
            $lead = $leads->random();
            $purchase = $property->acquisition_price ?? rand(180000, 300000);
            $sale = $purchase + rand(20000, 80000);
            $closing = rand(2000, 6000);
            $marketing = rand(500, 2500);
            $stage = Arr::random(['analyzing', 'contract_sent', 'under_contract', 'clear_to_close', 'closed_won', 'closed_lost']);

            $deal = Deal::create([
                'team_id' => $team->id,
                'property_id' => $property->id,
                'lead_id' => $lead->id,
                'name' => "Deal {$idx}",
                'contract_type' => Arr::random(['assignment', 'double_close', 'wholetail']),
                'contract_date' => Carbon::now()->subDays(rand(5, 45)),
                'closing_date' => Carbon::now()->addDays(rand(5, 60)),
                'purchase_price' => $purchase,
                'assignment_fee' => null,
                'sale_price' => $sale,
                'closing_costs' => $closing,
                'marketing_costs' => $marketing,
                'stage' => $stage,
                'profit' => $sale - ($purchase + $closing + $marketing),
                'roi' => round((($sale - ($purchase + $closing + $marketing)) / max($purchase + $closing + $marketing, 1)) * 100, 2),
            ]);

            if ($stage === 'closed_won') {
                $property->update(['status' => 'sold', 'sale_price' => $sale]);
            } elseif ($stage === 'under_contract') {
                $property->update(['status' => 'under_contract']);
            }

            $lead->update(['active_deal_id' => $deal->id]);
            $property->update(['active_deal_id' => $deal->id]);

            return $deal;
        });

        // Tasks
        $leads->take(6)->each(function (Lead $lead) use ($team, $users) {
            Task::create([
                'team_id' => $team->id,
                'title' => 'Call back seller',
                'description' => 'Confirm appointment time.',
                'due_date' => Carbon::now()->addDays(rand(0, 3)),
                'related_type' => 'Lead',
                'related_id' => $lead->id,
                'assigned_to_id' => $users->random()->id,
                'status' => Arr::random(['open', 'in_progress', 'done']),
                'priority' => Arr::random(['low', 'medium', 'high']),
            ]);
        });

        // Comms
        $leads->take(5)->each(function (Lead $lead) use ($team, $users) {
            CallLog::create([
                'team_id' => $team->id,
                'lead_id' => $lead->id,
                'user_id' => $users->random()->id,
                'called_at' => Carbon::now()->subDays(rand(0, 3)),
                'duration_minutes' => rand(1, 12),
                'outcome' => Arr::random(['no_answer', 'left_vm', 'spoke', 'follow_up']),
                'notes' => 'Demo call log',
            ]);

            SmsLog::create([
                'team_id' => $team->id,
                'lead_id' => $lead->id,
                'user_id' => $users->random()->id,
                'sent_at' => Carbon::now()->subDays(rand(0, 3)),
                'direction' => Arr::random(['inbound', 'outbound']),
                'message' => 'Demo SMS message body',
                'notes' => 'Demo sms log',
            ]);
        });
    }
}
