@php($title = 'Edit Lead')
@extends('layouts.app')
@section('content')
<div class="card">
    <h2 style="margin-top:0">Edit Lead</h2>
    <form method="POST" action="{{ route('leads.update', $lead) }}" class="grid">
        @csrf @method('PUT')
        <div class="row">
            <div>
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name', $lead->name) }}" required>
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $lead->email) }}">
            </div>
        </div>
        <div class="row">
            <div>
                <label>Phone</label>
                <input type="tel" name="phone" value="{{ old('phone', $lead->phone) }}">
            </div>
            <div>
                <label>Status</label>
                <input type="text" name="status" value="{{ old('status', $lead->status) }}">
            </div>
        </div>
        <div class="row">
            <div>
                <label>Source</label>
                <input type="text" name="source" value="{{ old('source', $lead->source) }}">
            </div>
            <div>
                <label>Notes</label>
                <input type="text" name="notes" value="{{ old('notes', $lead->notes) }}">
            </div>
        </div>
        <div>
            <button class="btn" type="submit">Update</button>
            <a class="btn" href="{{ route('leads.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection

