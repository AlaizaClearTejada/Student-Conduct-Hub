<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecisionKeyword extends Model
{
    protected $fillable = [
        'decision_rule_id',
        'keyword',
        'weight',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(DecisionRule::class, 'decision_rule_id');
    }
}
