<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'monthly_price', 'yearly_price', 'features', 'icon', 'color', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
        ];
    }
}
