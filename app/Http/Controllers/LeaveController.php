<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    /**
     * Display employee leave management dashboard with quotas and history
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $year = $request->year ? (int) $request->year : now()->year;

        // Quotas and balances
        $quotas = [
            'casual' => [
                'total' => $user->totalLeaveQuota('casual'),
                'used' => $user->usedLeaveDays('casual', $year),
                'remaining' => $user->remainingLeaveDays('casual', $year),
            ],
            'sick' => [
                'total' => $user->totalLeaveQuota('sick'),
                'used' => $user->usedLeaveDays('sick', $year),
                'remaining' => $user->remainingLeaveDays('sick', $year),
            ],
            'emergency' => [
                'total' => $user->totalLeaveQuota('emergency'),
                'used' => $user->usedLeaveDays('emergency', $year),
                'remaining' => $user->remainingLeaveDays('emergency', $year),
            ],
            'wfh' => [
                'total' => $user->totalLeaveQuota('wfh'),
                'used' => $user->usedLeaveDays('wfh', $year),
                'remaining' => $user->remainingLeaveDays('wfh', $year),
            ],
        ];

        // Leave history query
        $query = Leave::where('user_id', $user->id)
            ->whereYear('start_date', $year);

        if ($request->filled('type')) {
            $query->where('leave_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->latest('start_date')->paginate(10)->withQueryString();

        return view('leaves.index', compact('leaves', 'quotas', 'year'));
    }

    /**
     * Submit a new leave or WFH application
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'leave_type' => 'required|in:casual,sick,emergency,wfh',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'nullable|boolean',
            'half_day_type' => 'nullable|required_if:is_half_day,1|in:first_half,second_half',
            'reason' => 'required|string|min:5|max:1000',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $isHalfDay = $request->boolean('is_half_day');

        // Calculate business days (excluding weekends)
        if ($isHalfDay) {
            $daysCount = 0.5;
            $endDate = $startDate->copy();
        } else {
            $period = CarbonPeriod::create($startDate, $endDate);
            $businessDays = 0;
            foreach ($period as $date) {
                if (!$date->isWeekend()) {
                    $businessDays++;
                }
            }

            if ($businessDays === 0) {
                return back()->with('error', 'Selected leave dates only include weekends!');
            }

            $daysCount = (float) $businessDays;
        }

        // Quota check
        $remainingQuota = $user->remainingLeaveDays($validated['leave_type']);
        if ($daysCount > $remainingQuota) {
            return back()->with('error', "Insufficient {$validated['leave_type']} leave balance. Requested: {$daysCount} days, Remaining: {$remainingQuota} days.");
        }

        // Check overlapping existing pending or approved leaves
        $hasOverlap = Leave::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('start_date', '<=', $startDate->format('Y-m-d'))
                          ->where('end_date', '>=', $endDate->format('Y-m-d'));
                  });
            })->exists();

        if ($hasOverlap) {
            return back()->with('error', 'You already have an existing leave application covering this date range!');
        }

        Leave::create([
            'user_id' => $user->id,
            'leave_type' => $validated['leave_type'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'days_count' => $daysCount,
            'is_half_day' => $isHalfDay,
            'half_day_type' => $isHalfDay ? $request->half_day_type : null,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave application submitted successfully for ' . $daysCount . ' day(s). Awaiting manager approval.');
    }

    /**
     * Cancel own pending leave
     */
    public function destroy(Leave $leave)
    {
        if ($leave->user_id !== Auth::id()) {
            abort(403);
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be cancelled.');
        }

        $leave->delete();

        return back()->with('success', 'Leave application has been cancelled.');
    }

    /**
     * Admin view of all employee leave applications
     */
    public function adminIndex(Request $request)
    {
        $query = Leave::with(['user', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('leave_type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $pendingCount = Leave::where('status', 'pending')->count();
        $approvedCount = Leave::where('status', 'approved')->count();
        $rejectedCount = Leave::where('status', 'rejected')->count();

        $leaves = $query->latest()->paginate(15)->withQueryString();
        $employees = User::orderBy('name')->get();

        return view('admin.leaves.index', compact('leaves', 'employees', 'pendingCount', 'approvedCount', 'rejectedCount'));
    }

    /**
     * Admin Approve Leave & Auto-Sync with Attendance Records
     */
    public function approve(Request $request, Leave $leave)
    {
        $validated = $request->validate([
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $leave->update([
            'status' => 'approved',
            'admin_remarks' => $validated['admin_remarks'] ?? 'Approved by ' . Auth::user()->name,
            'approved_by' => Auth::id(),
            'actioned_at' => now(),
        ]);

        // Auto-sync into attendances table
        $startDate = Carbon::parse($leave->start_date);
        $endDate = Carbon::parse($leave->end_date);
        $period = CarbonPeriod::create($startDate, $endDate);

        $attendanceType = $leave->leave_type === 'wfh' ? 'wfh' : 'on_leave';

        foreach ($period as $date) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            $dateStr = $date->format('Y-m-d');

            Attendance::updateOrCreate(
                [
                    'user_id' => $leave->user_id,
                    'date' => $dateStr,
                ],
                [
                    'attendance_type' => $leave->is_half_day ? 'half_day' : $attendanceType,
                    'is_half_day' => $leave->is_half_day,
                    'leave_id' => $leave->id,
                    'status' => $leave->leave_type === 'wfh' ? 'On Time' : 'On Time',
                ]
            );
        }

        return back()->with('success', "Leave request #{$leave->id} approved and attendance synchronized successfully.");
    }

    /**
     * Admin Reject Leave
     */
    public function reject(Request $request, Leave $leave)
    {
        $validated = $request->validate([
            'admin_remarks' => 'required|string|max:500',
        ]);

        $leave->update([
            'status' => 'rejected',
            'admin_remarks' => $validated['admin_remarks'],
            'approved_by' => Auth::id(),
            'actioned_at' => now(),
        ]);

        // If previously synced attendance records exist for this leave, remove or revert them
        Attendance::where('leave_id', $leave->id)->update([
            'attendance_type' => 'present',
            'leave_id' => null,
        ]);

        return back()->with('success', "Leave request #{$leave->id} has been rejected.");
    }
}
