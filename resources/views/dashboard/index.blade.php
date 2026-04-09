@php($title = 'Dashboard')
@extends('layouts.app')
@section('content')
<style>
    .metric-cards{
        display:grid;
        grid-template-columns: repeat(auto-fit, minmax(160px,1fr));
        gap:.85rem;
    }
    .metric-cards .card{
        border-radius:14px;
        padding:1rem;
        min-height:120px;
    }
    .metric-cards h2{
        font-size:1.65rem;
        margin:.3rem 0 0;
    }
    .pipeline-board{
        margin-top:1.25rem;
        display:grid;
        grid-template-columns: repeat(auto-fit, minmax(180px,1fr));
        gap:.7rem;
    }
    .pipeline-column{
        background:#0f172a;
        border:1px solid #1f2a3b;
        border-radius:16px;
        min-height:240px;
        padding:.85rem;
        color:#e2e8f0;
        box-shadow:0 12px 30px rgba(0,0,0,.35);
    }
    .pipeline-column header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        font-weight:600;
        font-size:.95rem;
        margin-bottom:.6rem;
        border-bottom:1px solid rgba(255,255,255,.08);
        padding-bottom:.3rem;
    }
    .pipeline-column header span{
        font-size:.8rem;
        color:#94a3b8;
    }
    .pipeline-column button{
        background:none;
        border:none;
        color:#64748b;
        font-size:1.1rem;
        cursor:pointer;
    }
    .lead-card{
        background:#fff;
        border-radius:10px;
        border:1px solid #d6dae7;
        padding:.75rem;
        margin-bottom:.65rem;
        box-shadow:0 4px 12px rgba(15,23,42,.2);
    }
    .lead-card:last-child{margin-bottom:0;}
    .lead-title{
        font-weight:600;
        color:#0f172a;
        font-size:.88rem;
        margin:0;
    }
    .lead-sub{
        color:#475569;
        font-size:.8rem;
        margin-top:.1rem;
    }
    .status-pill{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:10px;
        border:1px solid #d1d5db;
        padding:.05rem .5rem;
        font-size:.68rem;
        font-weight:600;
        color:#0b4ea2;
        background:#ecfeff;
    }
    .lead-updated{
        color:#6b7280;
        font-size:.75rem;
        margin-top:.3rem;
    }
    .lead-meta{
        display:flex;
        justify-content:space-between;
        margin-top:.5rem;
        padding-top:.45rem;
        border-top:1px solid #e5e7eb;
        color:#1f2937;
        font-size:.74rem;
    }
    .lead-meta .label{
        color:#94a3b8;
        font-size:.65rem;
        text-transform:uppercase;
        letter-spacing:.06em;
    }
    .lead-footer{
        display:flex;
        gap:.4rem;
        margin-top:.6rem;
    }
    .chip{
        flex:1;
        text-align:center;
        border:1px solid #dfe5f0;
        border-radius:8px;
        padding:.3rem;
        font-size:.72rem;
        color:#0f172a;
        background:#f8fafc;
    }
    .empty-state{
        color:#7dd3fc;
        font-size:.85rem;
        text-align:center;
        padding:1.4rem .5rem;
        border:1px dashed rgba(255,255,255,.18);
        border-radius:12px;
        background:rgba(15,23,42,.45);
    }
</style>

<div class="metric-cards">
    <div class="card"><div class="muted">Total Leads</div><h2 style="margin:.2rem 0 0">{{ $metrics['total_leads'] }}</h2></div>
    <div class="card"><div class="muted">Total Properties</div><h2 style="margin:.2rem 0 0">{{ $metrics['total_properties'] }}</h2></div>
    <div class="card"><div class="muted">Conversion Rate</div><h2 style="margin:.2rem 0 0">{{ $metrics['conversion_rate'] }}%</h2></div>
    <div class="card"><div class="muted">Avg Price</div><h2 style="margin:.2rem 0 0">${{ $metrics['avg_property_price'] }}</h2></div>
</div>

<div class="metric-cards" style="margin-top:1rem;">
    <div class="card"><div class="muted">Calls Made Today</div><h2 style="margin:.2rem 0 0">{{ $metrics['calls_today'] }}</h2></div>
    <div class="card"><div class="muted">Talk Time Today</div><h2 style="margin:.2rem 0 0">{{ $metrics['talk_time_today'] }} min</h2></div>
</div>

<div class="pipeline-board">
    @foreach($boardColumns as $status => $leads)
        <div class="pipeline-column">
            <header>
                <div>
                    {{ $status }}
                    <span>({{ $leads->count() }})</span>
                </div>
                <button type="button" aria-label="Column actions">&#8230;</button>
            </header>

            @if($leads->isEmpty())
                <div class="empty-state">No leads in this stage.</div>
            @else
                @foreach($leads as $lead)
                <article class="lead-card">
                    <div style="display:flex; justify-content:space-between; gap:.5rem;">
                        <div>
                            <p class="lead-title">{{ $lead['name'] }}</p>
                            <p class="lead-sub">{{ $lead['email'] }}</p>
                            <p class="lead-sub">{{ $lead['phone'] }}</p>
                        </div>
                        <span class="status-pill">{{ $lead['status'] }}</span>
                    </div>
                    <div class="lead-updated">Updated: {{ $lead['updated'] }}</div>
                    <div class="lead-meta">
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
                        <span class="chip">{{ $lead['source'] }}</span>
                        <span class="chip">{{ $lead['notes'] }}</span>
                    </div>
                </article>
                @endforeach
            @endif
        </div>
    @endforeach
</div>
@endsection
