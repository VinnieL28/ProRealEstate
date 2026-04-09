@php($title = $pageTitle)
@extends('layouts.app')
@section('content')
<style>
    .board-shell{
        background:#fff;
        border-radius:18px;
        padding:1.25rem;
        border:1px solid #e2e8f0;
        box-shadow:0 25px 50px rgba(15,23,42,.06);
    }
    .board-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap:wrap;
        gap:1rem;
        margin-bottom:1rem;
    }
    .board-title{
        font-size:1.4rem;
        margin:0;
        color:#0f172a;
    }
    .board-crumb{
        text-transform:uppercase;
        color:#94a3b8;
        letter-spacing:.08em;
        font-size:.72rem;
        margin-bottom:.15rem;
    }
    .board-actions{
        display:flex;
        gap:.5rem;
        flex-wrap:wrap;
    }
    .board-actions button,
    .board-actions a{
        border:1px solid #cbd5f5;
        background:#fff;
        border-radius:10px;
        padding:.35rem .8rem;
        color:#1f2937;
        display:flex;
        align-items:center;
        gap:.35rem;
        font-size:.85rem;
        text-decoration:none;
    }
    .board-actions .primary{
        background:#0fadb5;
        color:#fff;
        border:none;
        box-shadow:0 8px 18px rgba(15,173,181,.35);
    }
    .lead-filters{
        display:flex;
        gap:.35rem;
        flex-wrap:wrap;
        margin-bottom:1.25rem;
    }
    .lead-chip{
        border:1px solid #d7dde8;
        background:#fff;
        border-radius:999px;
        padding:.25rem .9rem;
        font-size:.78rem;
        color:#475569;
        display:flex;
        align-items:center;
        gap:.3rem;
    }
    .lead-chip span{
        font-weight:600;
        color:#0f172a;
    }
    .lead-board-grid{
        display:grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap:.75rem;
    }
    .lead-stage{
        background:#f8fafc;
        border-radius:20px;
        border:1px solid #d7dde8;
        display:flex;
        flex-direction:column;
        min-height:260px;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.6);
    }
    .lead-stage header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        border-bottom:1px solid #e2e8f0;
        padding:.55rem .85rem;
        background:#0a2d79;
        color:#fff;
        font-weight:600;
        border-radius:20px 20px 0 0;
    }
    .lead-stage header span{
        font-weight:500;
        font-size:.78rem;
        margin-left:.35rem;
        color:rgba(255,255,255,.85);
    }
    .lead-stage header button{
        background:none;
        border:none;
        color:#dbeafe;
        font-size:1.2rem;
        cursor:pointer;
    }
    .lead-stage-body{
        padding:.85rem;
        flex:1;
        background:#fdfefe;
        border-radius:0 0 20px 20px;
    }
    .lead-card{
        border:1px solid #dfe5f0;
        border-radius:12px;
        padding:.85rem;
        background:#fff;
        box-shadow:0 3px 12px rgba(15,23,42,.08);
        margin-bottom:.75rem;
    }
    .lead-card:last-child{margin-bottom:0;}
    .lead-card h4{
        margin:0;
        font-size:.95rem;
        color:#0f172a;
    }
    .lead-card .subline{
        margin:.2rem 0;
        color:#475569;
        font-size:.85rem;
    }
    .status-pill{
        border:1px solid #d1d5db;
        background:#f0f9ff;
        color:#0b4ea2;
        border-radius:12px;
        padding:.1rem .6rem;
        font-size:.75rem;
        font-weight:600;
        min-width:52px;
        text-align:center;
    }
    .lead-meta-row{
        display:flex;
        justify-content:space-between;
        font-size:.78rem;
        margin-top:.55rem;
        padding-top:.45rem;
        border-top:1px solid #e2e8f0;
    }
    .lead-meta-row span{
        display:block;
    }
    .lead-meta-row .label{
        text-transform:uppercase;
        color:#94a3b8;
        font-size:.68rem;
        letter-spacing:.08em;
        margin-bottom:.15rem;
    }
    .lead-footer{
        display:flex;
        gap:.5rem;
        margin-top:.6rem;
    }
    .lead-footer button{
        flex:1;
        border:1px solid #dfe5f0;
        background:#f8fafc;
        border-radius:8px;
        padding:.35rem;
        font-size:.74rem;
        color:#0f172a;
    }
    .stage-empty{
        background:#edeff3;
        border:1px solid #d3d8e3;
        border-radius:10px;
        text-align:center;
        padding:1rem .5rem;
        font-size:.85rem;
        color:#5f6b81;
        font-weight:500;
    }
    @media (max-width:900px){
        .lead-board-grid{
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }
    }
</style>

<div class="board-shell">
    <div class="board-head">
        <div>
            <div class="board-crumb">{{ $context === 'warm' ? 'Warm Leads' : 'Leads' }}</div>
            <h1 class="board-title">{{ $pageTitle }} ({{ $totalLeads }})</h1>
        </div>
        <div class="board-actions">
            <button type="button">Select Records</button>
            <button type="button">Grid</button>
            <button type="button">List</button>
            <button type="button">Sorting</button>
            <a class="primary" href="{{ route('leads.create') }}">Add Lead</a>
        </div>
    </div>

    <div class="lead-filters">
        @foreach($filters as $filter)
            <div class="lead-chip">{{ $filter['label'] }} <span>({{ $filter['count'] }})</span></div>
        @endforeach
    </div>

    <div class="lead-board-grid">
        @foreach($board as $stage => $leads)
            @php($stageLabel = \App\Support\LeadPipeline::stages()[$stage] ?? $stage)
            <div class="lead-stage">
                <header>
                    <div>{{ $stageLabel }} <span>({{ $leads->count() }})</span></div>
                    <button type="button" aria-label="Stage actions">&#8942;</button>
                </header>
                <div class="lead-stage-body">
                    @if($leads->isEmpty())
                        <div class="stage-empty">No leads in this stage.</div>
                    @else
                        @foreach($leads as $lead)
                            <article class="lead-card">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:.5rem;">
                                    <div>
                                        <h4>{{ $lead['name'] }}</h4>
                                        <div class="subline">{{ $lead['email'] }}</div>
                                        <div class="subline">{{ $lead['phone'] }}</div>
                                        @if($lead['notes'])
                                            <div class="subline">{{ $lead['notes'] }}</div>
                                        @endif
                                    </div>
                                    <span class="status-pill">{{ $lead['status'] }}</span>
                                </div>
                                <div class="subline" style="margin-top:.5rem;">Updated: {{ $lead['updated'] }}</div>
                                <div class="lead-meta-row">
                                    <div>
                                        <span class="label">Created</span>
                                        <span>{{ $lead['created'] }}</span>
                                    </div>
                                    <div style="text-align:right;">
                                        <span class="label">In Pipeline</span>
                                        <span>{{ $lead['days_in_pipeline'] }}</span>
                                    </div>
                                </div>
                                <div class="lead-footer">
                                    <button type="button">No Activity</button>
                                    <button type="button">0/0 Tasks</button>
                                </div>
                            </article>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
