<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerLeadOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_lead_id',
        'buyer_name',
        'amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function sellerLead(): BelongsTo
    {
        return $this->belongsTo(SellerLead::class);
    }
}
