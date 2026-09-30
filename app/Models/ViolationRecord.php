<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ViolationRecord extends Model
{
    /** @use HasFactory<\Database\Factories\ViolationRecordFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'case_tracking_number',
        'student_id',
        'offense_id',
        'offense_count',
        'applied_sanction',
        'reported_by',
        'status',
        'investigation_type',
        'investigating_authority_id',
        'incident_description',
        'date_of_incident',
        'charge_filed_date',
        'answer_deadline',
        'hearing_scheduled_date',
        'decision_deadline',
        'appeal_deadline',
        'student_answer',
        'student_answer_submitted_date',
        'assigned_to_sdt',
        'sdt_members',
        'resolution_date',
        'resolution_notes',
        'committee_report',
        'final_decision',
        'decided_by',
        'access_log',
        'parent_notified',
        'parent_notification_date',
        'sanction_imposed_at',
        'sanction_effective_at',
        'settled_by',
        'action_taken',
        'notice_sent_at',
        'notice_sent_by',
        'notice_count',
        'conference_date',
        'conference_notes',
        'conference_held_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_incident' => 'date',
            'charge_filed_date' => 'date',
            'answer_deadline' => 'date',
            'hearing_scheduled_date' => 'date',
            'decision_deadline' => 'date',
            'appeal_deadline' => 'date',
            'student_answer_submitted_date' => 'date',
            'resolution_date' => 'date',
            'parent_notification_date' => 'date',
            'assigned_to_sdt' => 'boolean',
            'parent_notified' => 'boolean',
            'sdt_members' => 'array',
            'access_log' => 'array',
            'sanction_imposed_at' => 'datetime',
            'sanction_effective_at' => 'datetime',
            'notice_sent_at' => 'datetime',
            'conference_date' => 'datetime',
            'conference_held_at' => 'datetime',
        ];
    }

    /**
     * Get the student associated with this violation record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the staff member who reported this violation.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the offense rule associated with this violation.
     */
    public function offenseRule(): BelongsTo
    {
        return $this->belongsTo(OffenseRule::class, 'offense_id');
    }

    /**
     * Get the investigating authority (Dean/CEO) for this case.
     */
    public function investigatingAuthority(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigating_authority_id');
    }

    /**
     * Get the authority who made the final decision.
     */
    public function decisionMaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * Get the admin who sent the formal notice.
     */
    public function noticeSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notice_sent_by');
    }

    /**
     * Get all evidence files for this case.
     */
    public function evidence()
    {
        return $this->hasMany(CaseEvidence::class);
    }

    public function tribunalCase(): HasOne
    {
        return $this->hasOne(TribunalCase::class);
    }

    /**
     * Get all workflow logs for this case.
     */
    public function workflowLogs()
    {
        return $this->hasMany(CaseWorkflowLog::class);
    }

    /**
     * Scope query to only records for a specific student.
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope query by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query to active sanctions.
     */
    public function scopeActiveSanctions($query)
    {
        return $query->where('status', 'Sanction Active');
    }

    /**
     * Check if answer deadline has passed (for ex parte proceedings per CSU G.9).
     */
    public function isAnswerOverdue(): bool
    {
        if (! $this->answer_deadline) {
            return false;
        }

        return now()->isAfter($this->answer_deadline) && ! $this->student_answer_submitted_date;
    }

    /**
     * Check if decision deadline is approaching or overdue (CSU G.13).
     */
    public function isDecisionOverdue(): bool
    {
        if (! $this->decision_deadline) {
            return false;
        }

        return now()->isAfter($this->decision_deadline) && ! $this->final_decision;
    }

    /**
     * Generate unique case tracking number (CSU Section G.4).
     */
    public static function generateCaseTrackingNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $prefix = sprintf('CSU-%s-%s-', $year, $month);
        $highestSequence = self::query()
            ->where('case_tracking_number', 'like', $prefix.'%')
            ->pluck('case_tracking_number')
            ->merge(TribunalCase::query()
                ->where('case_number', 'like', $prefix.'%')
                ->pluck('case_number'))
            ->map(fn (string $caseNumber): int => (int) substr($caseNumber, strlen($prefix)))
            ->max() ?? 0;

        do {
            $caseNumber = $prefix.str_pad((string) ++$highestSequence, 4, '0', STR_PAD_LEFT);
        } while (self::query()->where('case_tracking_number', $caseNumber)->exists()
            || TribunalCase::query()->where('case_number', $caseNumber)->exists());

        return $caseNumber;
    }

    /**
     * Log access to this confidential record (CSU Section G.20).
     */
    public function logAccess(int $userId, string $purpose = 'View'): void
    {
        $log = $this->access_log ?? [];
        $log[] = [
            'user_id' => $userId,
            'accessed_at' => now()->toISOString(),
            'purpose' => $purpose,
            'ip' => request()->ip(),
        ];

        $this->update(['access_log' => $log]);

        CaseWorkflowLog::logAction(
            $this->id,
            $userId,
            'Record Accessed',
            $purpose
        );
    }

    /**
     * Check if violation is currently under active sanction.
     */
    public function isActive(): bool
    {
        return $this->status === 'Sanction Active';
    }

    /**
     * Check if violation has been resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'Resolved';
    }

    /**
     * Check if violation is pending review.
     */
    public function isPending(): bool
    {
        return $this->status === 'Pending Review';
    }

    /**
     * Check if violation has been appealed.
     */
    public function isAppealed(): bool
    {
        return $this->status === 'Appealed';
    }

    public function isNoticeSent(): bool
    {
        return $this->status === 'Notice Sent';
    }

    public function isUnderInvestigation(): bool
    {
        return $this->status === 'Under Investigation';
    }

    public function canSendNotice(): bool
    {
        return $this->status === 'Pending Review' || ($this->status === 'Notice Sent' && $this->notice_count < 3);
    }

    public function canSubmitAnswer(): bool
    {
        return $this->status === 'Notice Sent'
            && ! $this->student_answer_submitted_date
            && ! $this->isAnswerOverdue();
    }

    public function canScheduleConference(): bool
    {
        return $this->status === 'Under Investigation';
    }

    public function canApplySanction(): bool
    {
        return $this->status === 'Under Investigation';
    }

    /**
     * Get a human-readable label for who settled the case.
     */
    public function settledByLabel(): ?string
    {
        return match ($this->settled_by) {
            'Dean' => 'Settled by the Dean',
            'OSDW' => 'Settled by OSDW',
            default => null,
        };
    }
}
