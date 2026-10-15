<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TribunalCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'incident_report_id',
        'violation_record_id',
        'title',
        'respondent_name',
        'incident_date',
        'category',
        'priority',
        'description',
        'status',
        'is_formal_charge',
        'document_path',
        'document_disk',
        'searchable_text',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\CaseStatus::class,
            'incident_date' => 'date',
            'is_formal_charge' => 'boolean',
        ];
    }

    public function scopeForTribunal(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('is_formal_charge', true)
                ->orWhereHas('incidentReport', function (Builder $reportQuery): void {
                    $reportQuery
                        ->where('report_type', 'Formal Charge')
                        ->orWhereRaw('LOWER(severity) = ?', ['major'])
                        ->orWhereHas('offense', fn (Builder $offenseQuery) => $offenseQuery
                            ->whereRaw('LOWER(severity_level) = ?', ['major']));
                })
                ->orWhereHas('violationRecord.offenseRule', fn (Builder $offenseQuery) => $offenseQuery
                    ->whereRaw('LOWER(severity_level) = ?', ['major']));
        });
    }

    public function isEligibleForTribunal(): bool
    {
        return self::query()
            ->forTribunal()
            ->whereKey($this->getKey())
            ->exists();
    }

    public function incidentReport(): BelongsTo
    {
        return $this->belongsTo(IncidentReport::class);
    }

    public function violationRecord(): BelongsTo
    {
        return $this->belongsTo(ViolationRecord::class);
    }

    public function transitionTo(\App\Enums\CaseStatus $newStatus): void
    {
        $currentStatus = $this->status;

        if (! $currentStatus instanceof \App\Enums\CaseStatus
            || ! in_array($newStatus, $currentStatus->validTransitions(), true)) {
            $currentStatusValue = $currentStatus instanceof \App\Enums\CaseStatus
                ? $currentStatus->value
                : (string) $currentStatus;

            throw new \InvalidArgumentException("Invalid status transition from {$currentStatusValue} to {$newStatus->value}");
        }

        $this->update(['status' => $newStatus]);
    }

    /**
     * Get all notifications for this case.
     */
    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class, 'case_id');
    }
}
