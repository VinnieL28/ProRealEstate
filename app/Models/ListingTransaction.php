<?php

namespace App\Models;

class ListingTransaction extends Transaction
{
    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            $transaction->type = self::TYPE_LISTING;
        });

        static::addGlobalScope('listing', function ($builder) {
            $builder->where('type', self::TYPE_LISTING);
        });
    }
}
