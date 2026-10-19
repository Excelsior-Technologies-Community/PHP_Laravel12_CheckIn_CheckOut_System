<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'total_break_seconds',
        'total_work_seconds',
        'status',
        'late_minutes',
        'overtime_seconds',
        'early_departure_minutes',
        'is_half_day',
        'early_departure_reason',
        'attendance_type',
        'leave_id',
    ];

    protected $casts = [
        'date' => 'date',
        'is_half_day' => 'boolean',
    ];

    public function breaks(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class);
    }

    public function getWorkHoursFormatted(): string
    {
        if (!$this->total_work_seconds) {
            return '-';
        }

        $hours = floor($this->total_work_seconds / 3600);
        $minutes = floor(($this->total_work_seconds % 3600) / 60);

        return "{$hours}h {$minutes}m";
    }

    public function getOvertimeFormatted(): string
    {
        if (!$this->overtime_seconds) {
            return '0m';
        }

        $hours = floor($this->overtime_seconds / 3600);
        $minutes = floor(($this->overtime_seconds % 3600) / 60);

        if ($hours > 0) {
            return "+{$hours}h {$minutes}m";
        }

        return "+{$minutes}m";
    }

    public function getBreakFormatted(): string
    {
        if (!$this->total_break_seconds) {
            return '0m';
        }

        $minutes = floor($this->total_break_seconds / 60);
        return "{$minutes}m";
    }

    public function getStatusBadge(): string
    {
        if ($this->attendance_type === 'on_leave') {
            return '<span class="badge bg-primary">On Leave</span>';
        }

        if ($this->attendance_type === 'wfh') {
            return '<span class="badge bg-info text-dark">Work From Home</span>';
        }

        if ($this->is_half_day || $this->attendance_type === 'half_day') {
            return '<span class="badge bg-secondary">Half Day</span>';
        }

        if ($this->status === 'Late') {
            return '<span class="badge bg-warning text-dark">Late (' . $this->late_minutes . 'm)</span>';
        }

        return '<span class="badge bg-success">On Time</span>';
    }
}
