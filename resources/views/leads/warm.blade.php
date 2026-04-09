@php($title = $pageTitle)
@extends('layouts.app')
@section('content')
<style>
    .table-shell{
        background:#ffffff;
        border-radius:18px;
        border:1px solid #e2e8f0;
        padding:1.25rem;
        box-shadow:0 30px 60px rgba(15,23,42,.08);
    }
    .table-head{
        display:flex;
        justify-content:space-between;
        gap:1rem;
        align-items:center;
        flex-wrap:wrap;
        margin-bottom:1rem;
    }
    .table-head h1{
        margin:0;
        font-size:1.4rem;
        color:#0f172a;
    }
    .table-head .crumb{
        text-transform:uppercase;
        font-size:.72rem;
        letter-spacing:.08em;
        color:#a0aec0;
    }
    .warm-actions{
        display:flex;
        gap:.4rem;
        flex-wrap:wrap;
    }
    .warm-actions button,
    .warm-actions a{
        border:1px solid #cbd5f5;
        border-radius:10px;
        background:#fff;
        padding:.35rem .85rem;
        font-size:.85rem;
        color:#1f2937;
        display:flex;
        align-items:center;
        gap:.35rem;
        text-decoration:none;
    }
    .warm-actions .primary{
        background:#0fadb5;
        border:none;
        color:#fff;
        box-shadow:0 10px 20px rgba(15,173,181,.3);
    }
    .filter-row{
        display:flex;
        flex-wrap:wrap;
        gap:.4rem;
        margin-bottom:1rem;
    }
    .filter-chip{
        border:1px solid #d8dee9;
        border-radius:999px;
        background:#f9fafb;
        padding:.3rem .9rem;
        font-size:.78rem;
        color:#475569;
        display:flex;
        align-items:center;
        gap:.2rem;
    }
    .filter-chip span{
        color:#0f172a;
        font-weight:600;
    }
    .warm-table{
        width:100%;
        border-collapse:separate;
        border-spacing:0;
        font-size:.85rem;
    }
    .warm-table thead tr{
        background:#f7f9fc;
    }
    .warm-table th{
        text-align:left;
        padding:.6rem .75rem;
        font-size:.78rem;
        text-transform:uppercase;
        letter-spacing:.08em;
        color:#5f6b81;
        border-bottom:1px solid #e5e7eb;
        position:relative;
    }
    .warm-table th.sortable{
        padding-right:1.4rem;
    }
    .warm-table th.sortable::after{
        content:'\2195';
        position:absolute;
        right:.45rem;
        font-size:.7rem;
        color:#b3bfd3;
    }
    .warm-table td{
        padding:.75rem;
        border-bottom:1px solid #edf2f7;
        color:#0f172a;
    }
    .select-col{
        width:42px;
        text-align:center;
    }
    .select-col input[type=checkbox]{
        width:16px;
        height:16px;
        accent-color:#0fadb5;
    }
    .warm-table tbody tr:hover{
        background:#f7fbff;
    }
    .warm-empty{
        text-align:center;
        padding:2rem;
        color:#94a3b8;
        font-size:.95rem;
    }
    @media (max-width:1024px){
        .warm-table{
            display:block;
            overflow-x:auto;
            white-space:nowrap;
        }
    }
</style>

<div class="table-shell">
    <div class="table-head">
        <div>
            <div class="crumb">Warm Leads</div>
            <h1>{{ $pageTitle }} ({{ $totalLeads }})</h1>
        </div>
        <div class="warm-actions">
            <button type="button">Select Records</button>
            <button type="button">Columns</button>
            <button type="button">Manage Filter</button>
            <a class="primary" href="{{ route('leads.create') }}">Add Lead</a>
        </div>
    </div>

    <div class="filter-row">
        @foreach($filters as $filter)
            <div class="filter-chip">{{ $filter['label'] }} <span>({{ $filter['count'] }})</span></div>
        @endforeach
    </div>

    @php($columnCount = count($columns) + 1)
    <div style="overflow-x:auto;">
        <table class="warm-table">
            <thead>
                <tr>
                    <th class="select-col"><input type="checkbox" aria-label="Select all"></th>
                    @foreach($columns as $label => $key)
                        <th class="sortable">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    <tr>
                        <td class="select-col"><input type="checkbox" aria-label="Select lead"></td>
                        @foreach($columns as $label => $key)
                            <td>{{ $lead[$key] ?? '—' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $columnCount }}" class="warm-empty" style="background:#f9feff;">No records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

