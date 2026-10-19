<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display attendance page with Search, Filter & Dashboard Statistics
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $user = Auth::user();

        // Today's attendance for logged-in user
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->with('breaks')
            ->first();

        // Active break if any
        $activeBreak = null;
        if ($attendance) {
            $activeBreak = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance History Query
        |--------------------------------------------------------------------------
        */
        $query = Attendance::with(['user', 'breaks'])
            ->where('user_id', $user->id);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereDate('date', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'checked_in':
                    $query->whereNotNull('check_in')->whereNull('check_out');
                    break;
                case 'checked_out':
                    $query->whereNotNull('check_out');
                    break;
                case 'late':
                    $query->where('status', 'Late');
                    break;
                case 'overtime':
                    $query->where('overtime_seconds', '>', 0);
                    break;
                case 'early_departure':
                    $query->where('early_departure_minutes', '>', 0);
                    break;
                case 'half_day':
                    $query->where('is_half_day', true);
                    break;
                case 'on_leave':
                    $query->whereIn('attendance_type', ['on_leave', 'wfh']);
                    break;
            }
        }

        $attendanceHistory = $query
            ->latest('date')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Overall Statistics
        |--------------------------------------------------------------------------
        */
        $totalAttendance = Attendance::where('user_id', $user->id)->count();

        $completedAttendance = Attendance::where('user_id', $user->id)
            ->whereNotNull('check_out')
            ->count();

        $workingToday = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->exists();

        $totalBreakMinutes = floor(
            Attendance::where('user_id', $user->id)->sum('total_break_seconds') / 60
        );

        $totalWorkingHours = round(
            Attendance::where('user_id', $user->id)->sum('total_work_seconds') / 3600,
            2
        );

        $totalOvertimeHours = round(
            Attendance::where('user_id', $user->id)->sum('overtime_seconds') / 3600,
            2
        );

        $lateDays = Attendance::where('user_id', $user->id)
            ->where('status', 'Late')
            ->count();

        $earlyDepartureDays = Attendance::where('user_id', $user->id)
            ->where('early_departure_minutes', '>', 0)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Monthly Attendance Report & Filter
        |--------------------------------------------------------------------------
        */
        $currentMonth = $request->month
            ? Carbon::parse($request->month)->month
            : Carbon::now()->month;

        $currentYear = $request->month
            ? Carbon::parse($request->month)->year
            : Carbon::now()->year;

        $selectedMonth = Carbon::create($currentYear, $currentMonth, 1)->format('F Y');
        $selectedMonthRaw = Carbon::create($currentYear, $currentMonth, 1)->format('Y-m');

        $monthlyAttendance = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear);

        $monthlyPresentDays = (clone $monthlyAttendance)->whereIn('attendance_type', ['present', 'half_day'])->count();
        $monthlyLateDays = (clone $monthlyAttendance)->where('status', 'Late')->count();
        $monthlyOvertimeDays = (clone $monthlyAttendance)->where('overtime_seconds', '>', 0)->count();
        $monthlyEarlyDepartureDays = (clone $monthlyAttendance)->where('early_departure_minutes', '>', 0)->count();
        $monthlyHalfDays = (clone $monthlyAttendance)->where('is_half_day', true)->count();
        $monthlyLeaveDays = (clone $monthlyAttendance)->where('attendance_type', 'on_leave')->count();
        $monthlyWfhDays = (clone $monthlyAttendance)->where('attendance_type', 'wfh')->count();

        $monthlyWorkingSeconds = (clone $monthlyAttendance)->sum('total_work_seconds');
        $monthlyBreakSeconds = (clone $monthlyAttendance)->sum('total_break_seconds');
        $monthlyOvertimeSeconds = (clone $monthlyAttendance)->sum('overtime_seconds');

        $monthlyWorkingHours = round($monthlyWorkingSeconds / 3600, 2);
        $monthlyBreakHours = round($monthlyBreakSeconds / 3600, 2);
        $monthlyOvertimeHours = round($monthlyOvertimeSeconds / 3600, 2);
        $monthlyNetPayableHours = round(($monthlyWorkingSeconds + $monthlyOvertimeSeconds) / 3600, 2);

        $averageWorkingHours = $monthlyPresentDays > 0
            ? round($monthlyWorkingHours / $monthlyPresentDays, 2)
            : 0;

        $daysInMonth = Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;
        $attendancePercentage = $daysInMonth > 0
            ? round(($monthlyPresentDays / $daysInMonth) * 100, 1)
            : 0;

        return view('attendance', [
            'attendance' => $attendance,
            'activeBreak' => $activeBreak,
            'attendanceHistory' => $attendanceHistory,
            'totalAttendance' => $totalAttendance,
            'completedAttendance' => $completedAttendance,
            'workingToday' => $workingToday,
            'totalBreakMinutes' => $totalBreakMinutes,
            'totalWorkingHours' => $totalWorkingHours,
            'totalOvertimeHours' => $totalOvertimeHours,
            'lateDays' => $lateDays,
            'earlyDepartureDays' => $earlyDepartureDays,

            // Monthly Report
            'monthlyPresentDays' => $monthlyPresentDays,
            'monthlyLateDays' => $monthlyLateDays,
            'monthlyOvertimeDays' => $monthlyOvertimeDays,
            'monthlyEarlyDepartureDays' => $monthlyEarlyDepartureDays,
            'monthlyHalfDays' => $monthlyHalfDays,
            'monthlyLeaveDays' => $monthlyLeaveDays,
            'monthlyWfhDays' => $monthlyWfhDays,
            'monthlyWorkingHours' => $monthlyWorkingHours,
            'monthlyBreakHours' => $monthlyBreakHours,
            'monthlyOvertimeHours' => $monthlyOvertimeHours,
            'monthlyNetPayableHours' => $monthlyNetPayableHours,
            'averageWorkingHours' => $averageWorkingHours,
            'attendancePercentage' => $attendancePercentage,
            'selectedMonth' => $selectedMonth,
            'selectedMonthRaw' => $selectedMonthRaw,
        ]);
    }

    /**
     * Check In
     */
    public function checkIn(Request $request)
    {
        $today = Carbon::today();

        // Weekend restriction
        if (Carbon::now()->isWeekend()) {
            return back()->with('error', 'Weekend check-in is not allowed!');
        }

        // Check if already checked in today
        if (Attendance::where('user_id', Auth::id())->where('date', $today)->exists()) {
            return back()->with('error', 'You have already checked in today!');
        }

        $checkInTime = Carbon::now();
        $officeStart = Carbon::today()->setTime(9, 30, 0);

        $status = 'On Time';
        $lateMinutes = 0;

        if ($checkInTime->greaterThan($officeStart)) {
            $status = 'Late';
            $lateMinutes = (int) $officeStart->diffInMinutes($checkInTime);
        }

        Attendance::create([
            'user_id' => Auth::id(),
            'date' => $today,
            'check_in' => $checkInTime->format('H:i:s'),
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'attendance_type' => 'present',
        ]);

        return back()->with('success', 'Check-in recorded successfully! Status: ' . $status . ($lateMinutes > 0 ? " ({$lateMinutes} mins late)" : ''));
    }

    /**
     * Start Break
     */
    public function breakStart(Request $request)
    {
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return back()->with('error', 'Please check in first!');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You have already checked out for today!');
        }

        $activeBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if ($activeBreak) {
            return back()->with('error', 'A break is already active!');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->format('H:i:s'),
        ]);

        return back()->with('success', 'Break started at ' . Carbon::now()->format('h:i A'));
    }

    /**
     * End Break
     */
    public function breakEnd(Request $request)
    {
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return back()->with('error', 'No attendance record found for today.');
        }

        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if (!$break) {
            return back()->with('error', 'No active break found!');
        }

        $breakStart = Carbon::parse($break->break_start);
        $breakEnd = Carbon::now();
        $breakSeconds = $breakStart->diffInSeconds($breakEnd);

        $break->update([
            'break_end' => $breakEnd->format('H:i:s'),
            'break_seconds' => $breakSeconds,
        ]);

        $attendance->increment('total_break_seconds', $breakSeconds);

        $minutes = floor($breakSeconds / 60);
        return back()->with('success', "Break ended! Duration: {$minutes} minutes.");
    }

    /**
     * Feature 3: Check Out with Graceful Overtime & Early Departure Handling
     */
    public function checkOut(Request $request)
    {
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return back()->with('error', 'Please check in first!');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You have already checked out today!');
        }

        // Auto-end active break if user forgot to end it
        $activeBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if ($activeBreak) {
            $breakStart = Carbon::parse($activeBreak->break_start);
            $breakEnd = Carbon::now();
            $breakSeconds = $breakStart->diffInSeconds($breakEnd);

            $activeBreak->update([
                'break_end' => $breakEnd->format('H:i:s'),
                'break_seconds' => $breakSeconds,
            ]);

            $attendance->increment('total_break_seconds', $breakSeconds);
        }

        $checkIn = Carbon::parse($attendance->check_in);
        $checkOut = Carbon::now();

        $totalSeconds = $checkIn->diffInSeconds($checkOut);
        $workSeconds = max(0, $totalSeconds - $attendance->total_break_seconds);

        $standardShiftSeconds = 8 * 3600; // 8 hours = 28800s
        $halfDayThresholdSeconds = 4 * 3600; // 4 hours = 14400s

        $overtimeSeconds = 0;
        $earlyDepartureMinutes = 0;
        $isHalfDay = false;
        $attendanceType = 'present';

        if ($workSeconds > $standardShiftSeconds) {
            // Worked overtime
            $overtimeSeconds = $workSeconds - $standardShiftSeconds;
        } elseif ($workSeconds < $standardShiftSeconds) {
            // Left early
            $earlyDepartureMinutes = (int) ceil(($standardShiftSeconds - $workSeconds) / 60);

            if ($workSeconds < $halfDayThresholdSeconds) {
                // Worked less than 4 hours -> Half Day
                $isHalfDay = true;
                $attendanceType = 'half_day';
            }
        }

        $attendance->update([
            'check_out' => $checkOut->format('H:i:s'),
            'total_work_seconds' => $workSeconds,
            'overtime_seconds' => $overtimeSeconds,
            'early_departure_minutes' => $earlyDepartureMinutes,
            'is_half_day' => $isHalfDay,
            'attendance_type' => $attendanceType,
            'early_departure_reason' => $request->filled('early_departure_reason') ? $request->early_departure_reason : null,
        ]);

        $workedHours = floor($workSeconds / 3600);
        $workedMins = floor(($workSeconds % 3600) / 60);

        if ($overtimeSeconds > 0) {
            $otHours = floor($overtimeSeconds / 3600);
            $otMins = floor(($overtimeSeconds % 3600) / 60);
            return back()->with('success', "Check-out successful! Worked: {$workedHours}h {$workedMins}m with +{$otHours}h {$otMins}m Overtime recorded.");
        }

        if ($earlyDepartureMinutes > 0) {
            $statusText = $isHalfDay ? 'Half Day' : 'Early Departure';
            return back()->with('warning', "Check-out successful! Worked: {$workedHours}h {$workedMins}m ({$statusText}: Left {$earlyDepartureMinutes} mins before standard 8 hours).");
        }

        return back()->with('success', "Check-out successful! Full 8-hour shift completed ({$workedHours}h {$workedMins}m).");
    }

    /**
     * Feature 4: Interactive Monthly Attendance Calendar
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();

        $currentMonth = $request->month
            ? Carbon::parse($request->month)->month
            : Carbon::now()->month;

        $currentYear = $request->month
            ? Carbon::parse($request->month)->year
            : Carbon::now()->year;

        $startDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $selectedMonth = $startDate->format('F Y');
        $selectedMonthRaw = $startDate->format('Y-m');

        // Fetch attendances for the user in this month with breaks
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        // Build days data
        $calendarDays = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        $presentCount = 0;
        $lateCount = 0;
        $leaveCount = 0;
        $halfDayCount = 0;
        $absentCount = 0;

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $att = $attendances->get($dateStr);
            $isWeekend = $date->isWeekend();
            $isToday = $date->isToday();
            $isPast = $date->isPast() && !$isToday;

            $statusType = 'empty';
            $badgeColor = 'secondary';
            $statusLabel = 'N/A';

            if ($isWeekend) {
                $statusType = 'weekend';
                $badgeColor = 'light text-muted border';
                $statusLabel = 'Weekend';
            } elseif ($att) {
                if ($att->attendance_type === 'on_leave') {
                    $statusType = 'on_leave';
                    $badgeColor = 'primary';
                    $statusLabel = 'On Leave';
                    $leaveCount++;
                } elseif ($att->attendance_type === 'wfh') {
                    $statusType = 'wfh';
                    $badgeColor = 'info text-dark';
                    $statusLabel = 'WFH';
                    $presentCount++;
                } elseif ($att->is_half_day || $att->attendance_type === 'half_day') {
                    $statusType = 'half_day';
                    $badgeColor = 'dark';
                    $statusLabel = 'Half Day';
                    $halfDayCount++;
                } elseif ($att->status === 'Late') {
                    $statusType = 'late';
                    $badgeColor = 'warning text-dark';
                    $statusLabel = 'Late (' . $att->late_minutes . 'm)';
                    $lateCount++;
                    $presentCount++;
                } else {
                    $statusType = 'on_time';
                    $badgeColor = 'success';
                    $statusLabel = 'On Time';
                    $presentCount++;
                }
            } elseif ($isPast) {
                $statusType = 'absent';
                $badgeColor = 'danger';
                $statusLabel = 'Absent';
                $absentCount++;
            } else {
                $statusType = 'upcoming';
                $badgeColor = 'light text-muted';
                $statusLabel = 'Scheduled';
            }

            $calendarDays[] = [
                'date' => $dateStr,
                'day_number' => $date->day,
                'day_name' => $date->format('D'),
                'is_weekend' => $isWeekend,
                'is_today' => $isToday,
                'is_past' => $isPast,
                'attendance' => $att,
                'status_type' => $statusType,
                'badge_color' => $badgeColor,
                'status_label' => $statusLabel,
            ];
        }

        return view('attendance.calendar', [
            'calendarDays' => $calendarDays,
            'selectedMonth' => $selectedMonth,
            'selectedMonthRaw' => $selectedMonthRaw,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'leaveCount' => $leaveCount,
            'halfDayCount' => $halfDayCount,
            'absentCount' => $absentCount,
            'prevMonth' => $startDate->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $startDate->copy()->addMonth()->format('Y-m'),
        ]);
    }

    /**
     * Feature 8: Printable Monthly Timesheet (Payroll Ready)
     */
    public function timesheet(Request $request)
    {
        $user = Auth::user();

        // Admin can view other employees' timesheet
        if ($request->filled('user_id') && $user->isAdmin()) {
            $employee = User::findOrFail($request->user_id);
        } else {
            $employee = $user;
        }

        $currentMonth = $request->month
            ? Carbon::parse($request->month)->month
            : Carbon::now()->month;

        $currentYear = $request->month
            ? Carbon::parse($request->month)->year
            : Carbon::now()->year;

        $startDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $selectedMonth = $startDate->format('F Y');
        $selectedMonthRaw = $startDate->format('Y-m');

        // Fetch attendances for employee in this month
        $attendances = Attendance::with('breaks')
            ->where('user_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        $period = CarbonPeriod::create($startDate, $endDate);
        $timesheetRows = [];

        $totalWorkSecs = 0;
        $totalOvertimeSecs = 0;
        $totalBreakSecs = 0;
        $totalDaysPresent = 0;
        $totalDaysLate = 0;
        $totalDaysLeave = 0;

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $att = $attendances->get($dateStr);
            $isWeekend = $date->isWeekend();

            if ($att) {
                $totalWorkSecs += $att->total_work_seconds;
                $totalOvertimeSecs += $att->overtime_seconds;
                $totalBreakSecs += $att->total_break_seconds;

                if ($att->attendance_type === 'on_leave') {
                    $totalDaysLeave++;
                } else {
                    $totalDaysPresent++;
                    if ($att->status === 'Late') {
                        $totalDaysLate++;
                    }
                }
            }

            $timesheetRows[] = [
                'date' => $date->format('d M Y'),
                'day' => $date->format('l'),
                'is_weekend' => $isWeekend,
                'attendance' => $att,
            ];
        }

        $totalWorkHours = round($totalWorkSecs / 3600, 2);
        $totalOvertimeHours = round($totalOvertimeSecs / 3600, 2);
        $totalBreakHours = round($totalBreakSecs / 3600, 2);
        $netPayableHours = round(($totalWorkSecs + $totalOvertimeSecs) / 3600, 2);

        $allEmployees = $user->isAdmin() ? User::orderBy('name')->get() : collect([]);

        return view('attendance.timesheet', compact(
            'employee',
            'selectedMonth',
            'selectedMonthRaw',
            'timesheetRows',
            'totalWorkHours',
            'totalOvertimeHours',
            'totalBreakHours',
            'netPayableHours',
            'totalDaysPresent',
            'totalDaysLate',
            'totalDaysLeave',
            'allEmployees'
        ));
    }

    /**
     * Feature 8: Admin Bulk Export of all staff attendance in CSV for payroll
     */
    public function exportAllCsv(Request $request): StreamedResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $month = $request->month ? Carbon::parse($request->month) : Carbon::now();
        $startDate = $month->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $month->copy()->endOfMonth()->format('Y-m-d');

        $records = Attendance::with('user')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="all_staff_attendance_' . $month->format('Y_m') . '.csv"',
        ];

        $callback = function () use ($records, $month) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Employee ID',
                'Employee Name',
                'Email',
                'Department',
                'Date',
                'Type',
                'Check In',
                'Check Out',
                'Break (Mins)',
                'Work Hours',
                'Overtime Hours',
                'Late (Mins)',
                'Status',
                'Early Departure (Mins)',
                'Notes',
            ]);

            foreach ($records as $att) {
                $workHours = $att->total_work_seconds ? round($att->total_work_seconds / 3600, 2) : 0;
                $otHours = $att->overtime_seconds ? round($att->overtime_seconds / 3600, 2) : 0;

                fputcsv($file, [
                    $att->user->employee_id ?? 'EMP-' . $att->user->id,
                    $att->user->name,
                    $att->user->email,
                    $att->user->department ?? 'General',
                    $att->date->format('Y-m-d'),
                    ucwords(str_replace('_', ' ', $att->attendance_type)),
                    $att->check_in ?? '-',
                    $att->check_out ?? '-',
                    floor($att->total_break_seconds / 60),
                    $workHours,
                    $otHours,
                    $att->late_minutes ?? 0,
                    $att->status ?? 'On Time',
                    $att->early_departure_minutes ?? 0,
                    $att->early_departure_reason ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export single user attendance history as CSV
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Attendance::where('user_id', Auth::id());

        if ($request->filled('search')) {
            $query->whereDate('date', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'checked_in':
                    $query->whereNotNull('check_in')->whereNull('check_out');
                    break;
                case 'checked_out':
                    $query->whereNotNull('check_out');
                    break;
                case 'late':
                    $query->where('status', 'Late');
                    break;
                case 'overtime':
                    $query->where('overtime_seconds', '>', 0);
                    break;
            }
        }

        $records = $query->orderBy('date', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_history.csv"',
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date',
                'Type',
                'Check In',
                'Check Out',
                'Break (Minutes)',
                'Working Hours',
                'Overtime',
                'Punctuality',
                'Late Minutes',
                'Early Departure (Mins)',
            ]);

            foreach ($records as $attendance) {
                fputcsv($file, [
                    optional($attendance->date)->format('d-m-Y'),
                    ucwords(str_replace('_', ' ', $attendance->attendance_type)),
                    $attendance->check_in ?? '-',
                    $attendance->check_out ?? '-',
                    floor($attendance->total_break_seconds / 60),
                    $attendance->getWorkHoursFormatted(),
                    $attendance->getOvertimeFormatted(),
                    $attendance->status ?? 'On Time',
                    $attendance->late_minutes ?? 0,
                    $attendance->early_departure_minutes ?? 0,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
