<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_id',
        'department',
        'designation',
        'casual_leave_quota',
        'sick_leave_quota',
        'emergency_leave_quota',
        'wfh_quota',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'casual_leave_quota' => 'decimal:1',
            'sick_leave_quota' => 'decimal:1',
            'emergency_leave_quota' => 'decimal:1',
            'wfh_quota' => 'decimal:1',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Used leave days for a specific leave type in the current year
     */
    public function usedLeaveDays(string $type, ?int $year = null): float
    {
        $year = $year ?? now()->year;

        return (float) $this->leaves()
            ->where('leave_type', $type)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days_count');
    }

    /**
     * Total quota for a specific leave type
     */
    public function totalLeaveQuota(string $type): float
    {
        return match ($type) {
            'casual' => (float) $this->casual_leave_quota,
            'sick' => (float) $this->sick_leave_quota,
            'emergency' => (float) $this->emergency_leave_quota,
            'wfh' => (float) $this->wfh_quota,
            default => 0.0,
        };
    }

    /**
     * Remaining leave days for a specific leave type
     */
    public function remainingLeaveDays(string $type, ?int $year = null): float
    {
        $quota = $this->totalLeaveQuota($type);
        $used = $this->usedLeaveDays($type, $year);

        return max(0.0, $quota - $used);
    }
}
