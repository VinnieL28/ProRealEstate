<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'lead_id',
        'active_deal_id',
        'address',
        'city',
        'state',
        'zip',
        'type',
        'beds',
        'baths',
        'kitchens',
        'sqft',
        'year_built',
        'arv',
        'estimated_repairs',
        'estimated_rent',
        'acquisition_price',
        'sale_price',
        'status',
        'notes',
        'raw_api',
        'owner_name',
        'owner_mailing_address',
        'estimated_value',
        'estimated_total_liens',
        'estimated_equity',
        'lot_size',
        'basement_type',
        'basement_area',
        'garage_type',
        'garage_area',
        'total_assessed_value',
        'assessed_land_value',
        'assessed_improvement_value',
        'assessed_year',
        'tax_year',
        'property_taxes',
        'mortgage_amount',
        'mortgage_type',
        'interest_rate',
        'mortgage_term',
        'original_loan_date',
        'mortgage_maturity_date',
        'lender_name',
        'current_loan_balance',
        'last_sale_date',
        'ownership_duration_months',
        'last_sale_amount',
        'mls_status',
        'mls_listing_date',
        'mls_price',
        'mls_listing_type',
        'mls_days_on_market',
        'agent_name',
        'agent_phone',
        'agent_email',
        'owner_financing',
        'ownership_duration_years',
        'unit_count',
        'annual_taxes',
        'annual_insurance',
        'utilities_metered',
        'utilities_payer',
        'deferred_maintenance',
        'lease_type',
        'unit_mix',
        'debt_owed',
        'vacancies',
        'property_manager_name',
        'lease_start_date',
        'lease_end_date',
        'project_type',
        'sold_date',
        'holding_period_days',
    ];

    protected $casts = [
        'arv' => 'decimal:2',
        'estimated_repairs' => 'decimal:2',
        'estimated_rent' => 'decimal:2',
        'acquisition_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'raw_api' => 'array',
        'estimated_value' => 'decimal:2',
        'estimated_total_liens' => 'decimal:2',
        'estimated_equity' => 'decimal:2',
        'total_assessed_value' => 'decimal:2',
        'assessed_land_value' => 'decimal:2',
        'assessed_improvement_value' => 'decimal:2',
        'property_taxes' => 'decimal:2',
        'mortgage_amount' => 'decimal:2',
        'interest_rate' => 'decimal:3',
        'current_loan_balance' => 'decimal:2',
        'last_sale_amount' => 'decimal:2',
        'mls_price' => 'decimal:2',
        'annual_taxes' => 'decimal:2',
        'annual_insurance' => 'decimal:2',
        'debt_owed' => 'decimal:2',
        'lease_start_date' => 'date',
        'lease_end_date' => 'date',
        'sold_date' => 'date',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function activeDeal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'active_deal_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'related_id')->where('related_type', 'Property');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'related_id')->where('related_type', 'Property');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'related_id')->where('related_type', 'Property');
    }
}
