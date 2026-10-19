<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'days_count',
        'is_half_day',
        'half_day_type',
        'reason',
        'status',
        'admin_remarks',
        'approved_by',
        'actioned_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'decimal:1',
        'is_half_day' => 'boolean',
        'actioned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'approved' => '<span class="badge bg-success px-3 py-1"><i class="bi bi-check-circle me-1"></i>Approved</span>',
            'rejected' => '<span class="badge bg-danger px-3 py-1"><i class="bi bi-x-circle me-1"></i>Rejected</span>',
            default => '<span class="badge bg-warning text-dark px-3 py-1"><i class="bi bi-hourglass-split me-1"></i>Pending</span>',
        };
    }

    public function getTypeLabel(): string
    {
        return match ($this->leave_type) {
            'casual' => 'Casual Leave',
            'sick' => 'Sick Leave',
            'emergency' => 'Emergency Leave',
            'wfh' => 'Work From Home (WFH)',
            default => ucfirst($this->leave_type),
        };
    }

    public function getTypeBadge(): string
    {
        return match ($this->leave_type) {
            'casual' => '<span class="badge bg-primary">Casual</span>',
            'sick' => '<span class="badge bg-danger">Sick</span>',
            'emergency' => '<span class="badge bg-dark">Emergency</span>',
            'wfh' => '<span class="badge bg-info text-dark">WFH</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->leave_type) . '</span>',
        };
    }
}
