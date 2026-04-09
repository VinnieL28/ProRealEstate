<?php

namespace App\Models;

class InvestmentTransaction extends Transaction
{
    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            $transaction->type = self::TYPE_INVESTMENT;
        });

        static::addGlobalScope('investment', function ($builder) {
            $builder->where('type', self::TYPE_INVESTMENT);
        });
    }
}
