<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OffenseRule extends Model
{
    public const TRIBUNAL_SEVERITY_LEVELS = ['Major', 'Severe'];

    /** @use HasFactory<\Database\Factories\OffenseRuleFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'title',
        'description',
        'category',
        'severity_level',
        'gravity',
        'standard_sanction',
        'first_offense_sanction',
        'second_offense_sanction',
        'third_offense_sanction',
        'legal_reference',
        'requires_tribunal',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_tribunal' => 'boolean',
        ];
    }

    public function requiresTribunalReview(): bool
    {
        return in_array($this->severity_level, self::TRIBUNAL_SEVERITY_LEVELS, true);
    }

    public function scopeRequiringTribunalReview(Builder $query): Builder
    {
        return $query->whereIn('severity_level', self::TRIBUNAL_SEVERITY_LEVELS);
    }

    /**
     * Get all violation records associated with this offense rule.
     */
    public function violationRecords(): HasMany
    {
        return $this->hasMany(ViolationRecord::class, 'offense_id');
    }

    /**
     * Scope query to only active offense rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope query by severity level.
     */
    public function scopeSeverity($query, string $severity)
    {
        return $query->where('severity_level', $severity);
    }

    /**
     * Get the sanction for a specific offense count.
     * Implements CSU progressive discipline system.
     */
    public function getSanctionForOffenseCount(int $offenseCount): ?string
    {
        return match ($offenseCount) {
            1 => $this->first_offense_sanction,
            2 => $this->second_offense_sanction,
            3 => $this->third_offense_sanction,
            default => null,
        };
    }

    /**
     * Check if this offense requires Student Disciplinary Tribunal.
     * Summary investigations can be handled by Dean/CEO (CSU Section G.22).
     */
    public function requiresTribunal(): bool
    {
        return $this->requires_tribunal;
    }
}
