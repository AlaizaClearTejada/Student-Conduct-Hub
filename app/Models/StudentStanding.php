<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentStanding extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'students_standing';

    protected $fillable = [
        'student_id',
        'standing_status',
        'active_cases_count',
        'major_offenses_count',
        'pending_sanctions',
        'standing_computed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pending_sanctions' => 'array',
            'standing_computed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
