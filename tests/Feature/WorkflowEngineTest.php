<?php

use App\Events\LeadCreatedEvent;
use App\Models\Lead;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkflowRule;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->owner = User::factory()->create(['role' => 'owner']);
    $this->team  = Team::create([
        'name'             => 'WF Team',
        'owner_id'         => $this->owner->id,
        'default_currency' => 'USD',
    ]);
    $this->owner->update(['team_id' => $this->team->id]);
    $this->actingAs($this->owner);
});

it('dispatches LeadCreatedEvent when a lead is created', function () {
    Event::fake([LeadCreatedEvent::class]);

    Lead::create([
        'team_id'    => $this->team->id,
        'owner_name' => 'Trigger',
        'stage'      => 'new_lead',
    ]);

    Event::assertDispatched(LeadCreatedEvent::class);
});

it('creates a task via workflow when condition matches', function () {
    WorkflowRule::create([
        'team_id'   => $this->team->id,
        'name'      => 'Hot lead task',
        'trigger'   => 'lead_created',
        'is_active' => true,
        'conditions' => [
            ['field' => 'score', 'operator' => '>=', 'value' => 0],
        ],
        'actions' => [
            ['type' => 'create_task', 'title' => 'Call new lead', 'due_in_days' => 1, 'priority' => 'high'],
        ],
    ]);

    Lead::create([
        'team_id'    => $this->team->id,
        'owner_name' => 'Workflow Trigger',
        'phone'      => '555-1111',
        'stage'      => 'new_lead',
    ]);

    expect(\App\Models\Task::where('title', 'Call new lead')->count())->toBeGreaterThan(0);
});
