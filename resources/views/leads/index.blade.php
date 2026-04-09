@php($title = 'Leads')
@extends('layouts.app')
@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:.5rem; flex-wrap:wrap;">
        <h2 style="margin:0">Leads</h2>
        <div style="display:flex; gap:.5rem;">
            <a class="btn" href="{{ route('leads.create') }}">Add Lead</a>
            <form method="POST" action="{{ route('leads.import') }}" enctype="multipart/form-data" style="display:flex; gap:.5rem; align-items:center;">
                @csrf
                <input type="file" name="csv" accept=".csv" required>
                <button class="btn" type="submit">Import CSV</button>
            </form>
        </div>
    </div>
    <table style="margin-top:1rem;">
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Source</th><th></th></tr></thead>
        <tbody>
        @forelse($leads as $lead)
            <tr>
                <td>{{ $lead->name }}</td>
                <td>{{ $lead->email }}</td>
                <td>{{ $lead->phone }}</td>
                <td>{{ $lead->status }}</td>
                <td>{{ $lead->source }}</td>
                <td style="text-align:right;">
                    <a class="btn" href="{{ route('leads.edit', $lead) }}">Edit</a>
                    <form method="POST" action="{{ route('leads.destroy', $lead) }}" style="display:inline" onsubmit="return confirm('Delete lead?')">
                        @csrf @method('DELETE')
                        <button class="btn" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No leads yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:1rem;">{{ $leads->links() }}</div>
</div>
@endsection

