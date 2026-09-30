<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offense extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id',
        'filed_by',
        'case_number',
        'offense_type',
        'offense_category',
        'incident_date',
        'incident_time',
        'location',
        'description',
        'witnesses',
        'status',
        'tribunal_assigned_to',
        'tribunal_decision',
        'resolution_date',
        'sanction_details',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'resolution_date' => 'datetime',
            'witnesses' => 'array',
            'sanction_details' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    public function tribunalMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tribunal_assigned_to');
    }

    public function digitalEvidence(): HasMany
    {
        return $this->hasMany(DigitalEvidence::class);
    }
}
