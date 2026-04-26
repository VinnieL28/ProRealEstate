<?php

use App\Models\Lead;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create(['role' => 'owner']);
    $this->team  = Team::create([
        'name'             => 'Test Team',
        'owner_id'         => $this->owner->id,
        'default_currency' => 'USD',
    ]);
    $this->owner->update(['team_id' => $this->team->id]);
    $this->actingAs($this->owner);
});

it('creates a lead with team_id', function () {
    $lead = Lead::create([
        'team_id'    => $this->team->id,
        'owner_name' => 'John Smith',
        'phone'      => '555-1234',
        'stage'      => 'new_lead',
    ]);

    expect($lead->team_id)->toBe($this->team->id)
        ->and($lead->owner_name)->toBe('John Smith');
});

it('isolates leads between teams via HasTeamScope', function () {
    $otherOwner = User::factory()->create(['role' => 'owner']);
    $otherTeam = Team::create([
        'name'             => 'Other Team',
        'owner_id'         => $otherOwner->id,
        'default_currency' => 'USD',
    ]);
    $otherOwner->update(['team_id' => $otherTeam->id]);

    Lead::create(['team_id' => $this->team->id, 'owner_name' => 'Mine', 'stage' => 'new_lead']);
    Lead::create(['team_id' => $otherTeam->id, 'owner_name' => 'Theirs', 'stage' => 'new_lead']);

    $visible = Lead::all();

    expect($visible)->toHaveCount(1)
        ->and($visible->first()->owner_name)->toBe('Mine');
});

it('soft-deletes a lead instead of permanently deleting', function () {
    $lead = Lead::create([
        'team_id'    => $this->team->id,
        'owner_name' => 'Deletable',
        'stage'      => 'new_lead',
    ]);

    $lead->delete();

    expect(Lead::find($lead->id))->toBeNull()
        ->and(Lead::withTrashed()->find($lead->id))->not->toBeNull()
        ->and(Lead::withTrashed()->find($lead->id)->trashed())->toBeTrue();
});

it('calculates a lead score based on attributes', function () {
    $lead = Lead::create([
        'team_id'          => $this->team->id,
        'owner_name'       => 'Hot Seller',
        'phone'            => '555-9999',
        'email'            => 'hot@example.com',
        'stage'            => 'new_lead',
        'motivation_level' => 5,
        'asking_price'     => 200000,
    ]);

    $score = $lead->calculateScore();

    expect($score)->toBeInt()
        ->and($score)->toBeGreaterThan(0)
        ->and($score)->toBeLessThanOrEqual(100);
});
