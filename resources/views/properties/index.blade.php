@php($title = 'Properties')
@extends('layouts.app')
@section('content')
<div class="card">
    <h2 style="margin-top:0">Lookup Property by Address</h2>
    <form class="grid" method="POST" action="{{ route('properties.store') }}">
        @csrf
        <div style="display:grid; gap:.75rem;">
            <div style="display:grid; grid-template-columns:1fr auto; gap:.75rem;">
                <input id="address" name="address" type="text" value="{{ old('address') }}" placeholder="Enter property address..." required />
                <button class="btn" type="submit">Fetch & Save</button>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:.75rem;">
                <div>
                    <label class="muted" for="lead_id">Attach to Lead (optional)</label>
                    <select id="lead_id" name="lead_id">
                        <option value="">-- none --</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}" @selected(old('lead_id') == $lead->id)>{{ $lead->owner_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="muted" for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="1" placeholder="Quick notes...">{{ old('notes') }}</textarea>
                </div>
            </div>
            @error('address')
                <div class="muted">{{ $message }}</div>
            @enderror
        </div>
    </form>
</div>

<div style="height:1rem"></div>
<div class="card">
    <h3 style="margin-top:0" class="muted">Saved Properties</h3>
    @if (!empty($properties) && count($properties))
        <table>
            <thead>
                <tr>
                    <th style="width:35%">Address</th>
                    <th>City</th>
                    <th>Beds</th>
                    <th>Baths</th>
                    <th>Sqft</th>
                    <th>ARV</th>
                    <th>Acquisition</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($properties as $p)
                <tr>
                    <td>{{ $p->address }}</td>
                    <td>{{ $p->city }}</td>
                    <td>{{ $p->beds }}</td>
                    <td>{{ $p->baths }}</td>
                    <td>{{ $p->sqft }}</td>
                    <td>{{ $p->arv ? '$'.number_format($p->arv, 2) : '' }}</td>
                    <td>{{ $p->acquisition_price ? '$'.number_format($p->acquisition_price, 2) : '' }}</td>
                    <td>{{ ucfirst($p->status ?? 'prospect') }}</td>
                    <td><a class="btn" href="{{ url('/admin/properties/'.$p->id.'/edit') }}">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="muted">No properties found yet.</p>
    @endif
</div>
@endsection
