<?php

use App\Models\DripCampaign;
use App\Models\DripEnrollment;
use App\Models\DripStep;
use App\Models\Lead;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->owner = User::factory()->create(['role' => 'owner']);
    $this->team  = Team::create([
        'name'             => 'Drip Team',
        'owner_id'         => $this->owner->id,
        'default_currency' => 'USD',
    ]);
    $this->owner->update(['team_id' => $this->team->id]);
    $this->actingAs($this->owner);

    $this->lead = Lead::create([
        'team_id'    => $this->team->id,
        'owner_name' => 'Drip Target',
        'email'      => 'drip@example.com',
        'phone'      => '555-3333',
        'stage'      => 'new_lead',
    ]);
});

it('advances enrollment to next step after processing', function () {
    $campaign = DripCampaign::create([
        'team_id'      => $this->team->id,
        'name'         => 'Welcome',
        'trigger_type' => 'manual',
        'is_active'    => true,
    ]);

    DripStep::create([
        'campaign_id' => $campaign->id,
        'step_order'  => 1,
        'channel'     => 'email',
        'delay_days'  => 0,
        'subject'     => 'Hi',
        'body'        => 'Hello {{first_name}}',
    ]);
    DripStep::create([
        'campaign_id' => $campaign->id,
        'step_order'  => 2,
        'channel'     => 'email',
        'delay_days'  => 3,
        'subject'     => 'Following up',
        'body'        => 'Just checking in',
    ]);

    $enrollment = DripEnrollment::create([
        'campaign_id'  => $campaign->id,
        'team_id'      => $this->team->id,
        'lead_id'      => $this->lead->id,
        'current_step' => 0,
        'status'       => 'active',
        'enrolled_at'  => now(),
        'next_send_at' => now()->subMinute(),
    ]);

    Artisan::call('drip:process');

    $enrollment->refresh();
    expect($enrollment->current_step)->toBe(1)
        ->and($enrollment->status)->toBe('active');
});

it('marks enrollment completed after final step', function () {
    $campaign = DripCampaign::create([
        'team_id'      => $this->team->id,
        'name'         => 'Single Step',
        'trigger_type' => 'manual',
        'is_active'    => true,
    ]);

    DripStep::create([
        'campaign_id' => $campaign->id,
        'step_order'  => 1,
        'channel'     => 'email',
        'delay_days'  => 0,
        'subject'     => 'Only step',
        'body'        => 'Bye',
    ]);

    $enrollment = DripEnrollment::create([
        'campaign_id'  => $campaign->id,
        'team_id'      => $this->team->id,
        'lead_id'      => $this->lead->id,
        'current_step' => 0,
        'status'       => 'active',
        'enrolled_at'  => now(),
        'next_send_at' => now()->subMinute(),
    ]);

    Artisan::call('drip:process');

    $enrollment->refresh();
    expect($enrollment->status)->toBe('completed');
});

it('skips enrollments for paused campaigns', function () {
    $campaign = DripCampaign::create([
        'team_id'      => $this->team->id,
        'name'         => 'Paused',
        'trigger_type' => 'manual',
        'is_active'    => false,
    ]);

    DripStep::create([
        'campaign_id' => $campaign->id,
        'step_order'  => 1,
        'channel'     => 'email',
        'subject'     => 'x',
        'body'        => 'x',
        'delay_days'  => 0,
    ]);

    $enrollment = DripEnrollment::create([
        'campaign_id'  => $campaign->id,
        'team_id'      => $this->team->id,
        'lead_id'      => $this->lead->id,
        'current_step' => 0,
        'status'       => 'active',
        'enrolled_at'  => now(),
        'next_send_at' => now()->subMinute(),
    ]);

    Artisan::call('drip:process');

    $enrollment->refresh();
    expect($enrollment->status)->toBe('paused')
        ->and($enrollment->current_step)->toBe(0);
});
