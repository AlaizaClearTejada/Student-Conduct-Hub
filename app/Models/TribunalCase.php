<?php

namespace App\Models;

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
        'description',
        'status',
        'document_path',
        'document_disk',
        'searchable_text',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\CaseStatus::class,
        ];
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
        if (! in_array($newStatus, $this->status->validTransitions(), true)) {
            throw new \Exception("Invalid status transition from {$this->status->value} to {$newStatus->value}");
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
