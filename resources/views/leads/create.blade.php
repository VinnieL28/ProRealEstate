@php($title = 'Add Lead')
@extends('layouts.app')
@section('content')
<div class="card">
    <h2 style="margin-top:0">Add Lead</h2>
    <form method="POST" action="{{ route('leads.store') }}" class="grid">
        @csrf
        <div class="row">
            <div>
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email">
            </div>
        </div>
        <div class="row">
            <div>
                <label>Phone</label>
                <input type="tel" name="phone">
            </div>
            <div>
                <label>Status</label>
                <input type="text" name="status" placeholder="New / Contacted / Closed">
            </div>
        </div>
        <div class="row">
            <div>
                <label>Source</label>
                <input type="text" name="source" placeholder="Web / Referral / Campaign">
            </div>
            <div>
                <label>Notes</label>
                <input type="text" name="notes">
            </div>
        </div>
        <div>
            <button class="btn" type="submit">Create</button>
            <a class="btn" href="{{ route('leads.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection

