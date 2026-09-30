<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DigitalEvidence extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'digital_evidence';

    public $timestamps = false; // Using only uploaded_at

    protected $fillable = [
        'offense_id',
        'file_name',
        'file_type',
        's3_key',
        'file_size',
        'virus_scan_status',
        'uploaded_by',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

    public function offense(): BelongsTo
    {
        return $this->belongsTo(Offense::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
