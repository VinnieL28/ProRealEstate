@php($title = 'Edit Property')
@extends('layouts.app')
@section('content')
<div class="card">
    <h2 style="margin-top:0">Edit Property</h2>
    <p><a href="{{ route('properties.index') }}">← Back</a></p>

    <form method="POST" action="{{ route('properties.update', $property) }}">
        @csrf
        @method('PUT')

                <div class="section">
                    <div class="field">
                        <label for="address">Address</label>
                        <input id="address" name="address" type="text" value="{{ old('address', $property->address) }}">
                    </div>
                </div>

                <div class="section">
                    <h3>Basics</h3>
                    <div class="grid-3">
                        <div class="field"><label>Beds</label><input name="bedrooms" type="number" step="1" value="{{ old('bedrooms', $property->bedrooms) }}"></div>
                        <div class="field"><label>Baths</label><input name="bathrooms" type="number" step="1" value="{{ old('bathrooms', $property->bathrooms) }}"></div>
                        <div class="field"><label>Kitchens</label><input name="kitchens" type="number" step="1" value="{{ old('kitchens', $property->kitchens) }}"></div>
                        <div class="field"><label>Sqft</label><input name="sqft" type="number" step="1" value="{{ old('sqft', $property->sqft) }}"></div>
                        <div class="field"><label>Price</label><input name="price" type="number" step="0.01" value="{{ old('price', $property->price) }}"></div>
                    </div>
                </div>

                <div class="section">
                    <h3>Owner</h3>
                    <div class="grid-2">
                        <div class="field"><label>Owner Name</label><input name="owner_name" type="text" value="{{ old('owner_name', $property->owner_name) }}"></div>
                        <div class="field"><label>Owner Mailing Address</label><input name="owner_mailing_address" type="text" value="{{ old('owner_mailing_address', $property->owner_mailing_address) }}"></div>
                    </div>
                </div>

                <div class="section">
                    <h3>Valuation</h3>
                    <div class="grid-3">
                        <div class="field"><label>Estimated Value</label><input name="estimated_value" type="number" step="0.01" value="{{ old('estimated_value', $property->estimated_value) }}"></div>
                        <div class="field"><label>Estimated Total Liens</label><input name="estimated_total_liens" type="number" step="0.01" value="{{ old('estimated_total_liens', $property->estimated_total_liens) }}"></div>
                        <div class="field"><label>Estimated Equity</label><input name="estimated_equity" type="number" step="0.01" value="{{ old('estimated_equity', $property->estimated_equity) }}"></div>
                    </div>
                </div>

                <div class="section">
                    <h3>Property Info</h3>
                    <div class="grid-3">
                        <div class="field"><label>Property Type</label><input name="property_type" type="text" value="{{ old('property_type', $property->property_type) }}"></div>
                        <div class="field"><label>Lot Size</label><input name="lot_size" type="number" step="1" value="{{ old('lot_size', $property->lot_size) }}"></div>
                        <div class="field"><label>Year Built</label><input name="year_built" type="number" step="1" value="{{ old('year_built', $property->year_built) }}"></div>
                        <div class="field"><label>Basement Type</label><input name="basement_type" type="text" value="{{ old('basement_type', $property->basement_type) }}"></div>
                        <div class="field"><label>Basement Area</label><input name="basement_area" type="number" step="1" value="{{ old('basement_area', $property->basement_area) }}"></div>
                        <div class="field"><label>Garage Type</label><input name="garage_type" type="text" value="{{ old('garage_type', $property->garage_type) }}"></div>
                        <div class="field"><label>Garage Area</label><input name="garage_area" type="number" step="1" value="{{ old('garage_area', $property->garage_area) }}"></div>
                    </div>
                </div>

                <div class="section">
                    <h3>Tax Info</h3>
                    <div class="grid-3">
                        <div class="field"><label>Total Assessed Value</label><input name="total_assessed_value" type="number" step="0.01" value="{{ old('total_assessed_value', $property->total_assessed_value) }}"></div>
                        <div class="field"><label>Assessed Land Value</label><input name="assessed_land_value" type="number" step="0.01" value="{{ old('assessed_land_value', $property->assessed_land_value) }}"></div>
                        <div class="field"><label>Assessed Improvement Value</label><input name="assessed_improvement_value" type="number" step="0.01" value="{{ old('assessed_improvement_value', $property->assessed_improvement_value) }}"></div>
                        <div class="field"><label>Assessed Year</label><input name="assessed_year" type="number" step="1" value="{{ old('assessed_year', $property->assessed_year) }}"></div>
                        <div class="field"><label>Tax Year</label><input name="tax_year" type="number" step="1" value="{{ old('tax_year', $property->tax_year) }}"></div>
                        <div class="field"><label>Property Taxes</label><input name="property_taxes" type="number" step="0.01" value="{{ old('property_taxes', $property->property_taxes) }}"></div>
                    </div>
                </div>

                <div class="section">
                    <h3>Mortgage</h3>
                    <div class="grid-3">
                        <div class="field"><label>Amount</label><input name="mortgage_amount" type="number" step="0.01" value="{{ old('mortgage_amount', $property->mortgage_amount) }}"></div>
                        <div class="field"><label>Type</label><input name="mortgage_type" type="text" value="{{ old('mortgage_type', $property->mortgage_type) }}"></div>
                        <div class="field"><label>Interest Rate</label><input name="interest_rate" type="number" step="0.01" value="{{ old('interest_rate', $property->interest_rate) }}"></div>
                        <div class="field"><label>Mortgage Term</label><input name="mortgage_term" type="number" step="1" value="{{ old('mortgage_term', $property->mortgage_term) }}"></div>
                        <div class="field"><label>Original Loan Date</label><input name="original_loan_date" type="date" value="{{ old('original_loan_date', $property->original_loan_date) }}"></div>
                        <div class="field"><label>Maturity Date</label><input name="mortgage_maturity_date" type="date" value="{{ old('mortgage_maturity_date', $property->mortgage_maturity_date) }}"></div>
                        <div class="field"><label>Lender Name</label><input name="lender_name" type="text" value="{{ old('lender_name', $property->lender_name) }}"></div>
                        <div class="field"><label>Current Loan Balance</label><input name="current_loan_balance" type="number" step="0.01" value="{{ old('current_loan_balance', $property->current_loan_balance) }}"></div>
                    </div>
                </div>

                <div class="section">
                    <h3>MLS</h3>
                    <div class="grid-3">
                        <div class="field"><label>Status</label><input name="mls_status" type="text" value="{{ old('mls_status', $property->mls_status) }}"></div>
                        <div class="field"><label>Listing Date</label><input name="mls_listing_date" type="date" value="{{ old('mls_listing_date', $property->mls_listing_date) }}"></div>
                        <div class="field"><label>Price</label><input name="mls_price" type="number" step="0.01" value="{{ old('mls_price', $property->mls_price) }}"></div>
                        <div class="field"><label>Listing Type</label><input name="mls_listing_type" type="text" value="{{ old('mls_listing_type', $property->mls_listing_type) }}"></div>
                        <div class="field"><label>Days on Market</label><input name="mls_days_on_market" type="number" step="1" value="{{ old('mls_days_on_market', $property->mls_days_on_market) }}"></div>
                        <div class="field"><label>Agent Name</label><input name="agent_name" type="text" value="{{ old('agent_name', $property->agent_name) }}"></div>
                        <div class="field"><label>Agent Phone</label><input name="agent_phone" type="text" value="{{ old('agent_phone', $property->agent_phone) }}"></div>
                        <div class="field"><label>Agent Email</label><input name="agent_email" type="text" value="{{ old('agent_email', $property->agent_email) }}"></div>
                    </div>
                </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">Update</button>
            <a class="btn" href="{{ route('properties.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
