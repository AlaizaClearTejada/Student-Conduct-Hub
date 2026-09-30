<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IncidentReport extends Model
{
    /** @use HasFactory<\Database\Factories\IncidentReportFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tracking_number',
        'reporter_id',
        'student_id',
        'offense_id',
        'report_type',
        'description',
        'evidence_path',
        'status',
        'recommended_violation',
        'recommended_sanction',
        'severity',
        'decision_score',
        'matched_keywords',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'matched_keywords' => 'array',
        ];
    }

    /**
     * Get the staff member who reported this incident.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the student involved in this incident.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the offense rule associated with this incident.
     */
    public function offense(): BelongsTo
    {
        return $this->belongsTo(OffenseRule::class, 'offense_id');
    }

    public function tribunalCase(): HasOne
    {
        return $this->hasOne(TribunalCase::class);
    }
}
