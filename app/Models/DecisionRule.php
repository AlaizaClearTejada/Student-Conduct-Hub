<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DecisionRule extends Model
{
    protected $fillable = [
        'violation_name',
        'description',
        'severity',
        'recommended_action',
        'threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(DecisionKeyword::class);
    }
}
