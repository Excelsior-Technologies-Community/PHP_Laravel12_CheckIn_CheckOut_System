<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // Display attendance page with Search, Filter & Dashboard Statistics
    public function index(Request $request)
    {
        $today = Carbon::today();

        // Today's attendance for logged-in user
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $today)
            ->with('breaks')
            ->first();

        /*
    |--------------------------------------------------------------------------
    | Attendance History Query
    |--------------------------------------------------------------------------
    */
        $query = Attendance::with('user')
            ->where('user_id', Auth::id());

        /*
    |--------------------------------------------------------------------------
    | Search by Date
    | Example: 2026-07-23
    |--------------------------------------------------------------------------
    */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->whereDate('date', 'like', "%{$search}%");
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Status Filter
    |--------------------------------------------------------------------------
    */

        if ($request->filled('status')) {

            switch ($request->status) {

                case 'checked_in':
                    $query->whereNotNull('check_in')
                        ->whereNull('check_out');
                    break;

                case 'checked_out':
                    $query->whereNotNull('check_out');
                    break;

                case 'working':
                    $query->whereNull('check_out');
                    break;

                case 'completed':
                    $query->where('total_work_seconds', '>', 0);
                    break;
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Attendance History
    |--------------------------------------------------------------------------
    */

        $attendanceHistory = $query
            ->oldest('date')
            ->paginate(5)
            ->withQueryString();

        /*
    |--------------------------------------------------------------------------
    | Dashboard Statistics
    |--------------------------------------------------------------------------
    */

        $totalAttendance = Attendance::where('user_id', Auth::id())->count();

        $completedAttendance = Attendance::where('user_id', Auth::id())
            ->whereNotNull('check_out')
            ->count();

        $workingToday = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $today)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->exists();

        $totalBreakMinutes = floor(
            Attendance::where('user_id', Auth::id())
                ->sum('total_break_seconds') / 60
        );

        $totalWorkingHours = round(
            Attendance::where('user_id', Auth::id())
                ->sum('total_work_seconds') / 3600,
            2
        );

        return view('attendance', [
            'attendance'          => $attendance,
            'attendanceHistory'   => $attendanceHistory,
            'totalAttendance'     => $totalAttendance,
            'completedAttendance' => $completedAttendance,
            'workingToday'        => $workingToday,
            'totalBreakMinutes'   => $totalBreakMinutes,
            'totalWorkingHours'   => $totalWorkingHours,
        ]);
    }

    // Check In
    public function checkIn(Request $request)
    {
        $today = Carbon::today();

        // Weekend restriction
        if (Carbon::now()->isWeekend()) {
            return back()->with('error', 'Weekend check-in not allowed!');
        }

        // Check if already checked in today
        if (Attendance::where('user_id', Auth::id())->where('date', $today)->exists()) {
            return back()->with('error', 'Already checked in today!');
        }

        // Create attendance record
        Attendance::create([
            'user_id' => Auth::id(),
            'date' => $today,
            'check_in' => Carbon::now()->format('H:i:s')
        ]);

        return back()->with('success', 'Check-in successful!');
    }

    // Start Break
    public function breakStart(Request $request)
    {
        $today = Carbon::today();

        // Get today's attendance
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();

        // Check if user has checked in
        if (!$attendance || !$attendance->check_in) {
            return back()->with('error', 'Please check in first!');
        }

        // Check if break already started
        $activeBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if ($activeBreak) {
            return back()->with('error', 'Break already running!');
        }

        // Create new break
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->format('H:i:s')
        ]);

        return back()->with('success', 'Break started!');
    }

    // End Break
    public function breakEnd(Request $request)
    {
        $today = Carbon::today();

        // Get today's attendance
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();

        // Find active break
        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if (!$break) {
            return back()->with('error', 'No active break found!');
        }

        // Calculate break duration in seconds
        $breakStart = Carbon::parse($break->break_start);
        $breakEnd = Carbon::now();
        $breakSeconds = $breakStart->diffInSeconds($breakEnd);

        // Update break record
        $break->update([
            'break_end' => $breakEnd->format('H:i:s'),
            'break_seconds' => $breakSeconds
        ]);

        // Update total break seconds in attendance
        $attendance->increment('total_break_seconds', $breakSeconds);

        return back()->with('success', 'Break ended! Duration: ' . floor($breakSeconds / 60) . ' minutes');
    }

    // Check Out
    public function checkOut(Request $request)
    {
        $today = Carbon::today();

        // Get today's attendance
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();

        // Check if user has checked in
        if (!$attendance || !$attendance->check_in) {
            return back()->with('error', 'Please check in first!');
        }

        // Check if already checked out
        if ($attendance->check_out) {
            return back()->with('error', 'Already checked out today!');
        }

        // Calculate total time
        $checkIn = Carbon::parse($attendance->check_in);
        $checkOut = Carbon::now();

        $totalSeconds = $checkIn->diffInSeconds($checkOut);
        $workSeconds = max(0, $totalSeconds - $attendance->total_break_seconds);

        // Minimum 8 hours validation
        if ($workSeconds < (8 * 3600)) {
            $remaining = (8 * 3600) - $workSeconds;
            $minutes = ceil($remaining / 60);
            return back()->with('error', "Minimum 8 working hours required. Need {$minutes} more minutes.");
        }

        // Update attendance with check out
        $attendance->update([
            'check_out' => $checkOut->format('H:i:s'),
            'total_work_seconds' => $workSeconds
        ]);

        $hours = floor($workSeconds / 3600);
        $minutes = floor(($workSeconds % 3600) / 60);

        return back()->with('success', "Check-out successful! Worked: {$hours} hours {$minutes} minutes");
    }

    /**
     * Export Attendance History as CSV
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Attendance::where('user_id', Auth::id());

        /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */
        if ($request->filled('search')) {
            $query->whereDate('date', 'like', '%' . $request->search . '%');
        }

        /*
    |--------------------------------------------------------------------------
    | Status Filter
    |--------------------------------------------------------------------------
    */
        if ($request->filled('status')) {

            switch ($request->status) {

                case 'checked_in':
                    $query->whereNotNull('check_in')
                        ->whereNull('check_out');
                    break;

                case 'checked_out':
                    $query->whereNotNull('check_out');
                    break;

                case 'working':
                    $query->whereNull('check_out');
                    break;

                case 'completed':
                    $query->where('total_work_seconds', '>', 0);
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
                'Check In',
                'Check Out',
                'Break (Minutes)',
                'Working Hours',
                'Status',
            ]);

            foreach ($records as $attendance) {

                $workHours = '';

                if ($attendance->total_work_seconds) {

                    $hours = floor($attendance->total_work_seconds / 3600);
                    $minutes = floor(($attendance->total_work_seconds % 3600) / 60);

                    $workHours = "{$hours}h {$minutes}m";
                }

                if ($attendance->check_out) {
                    $status = 'Completed';
                } elseif ($attendance->check_in) {
                    $status = 'Working';
                } else {
                    $status = 'Absent';
                }

                fputcsv($file, [
                    optional($attendance->date)->format('d-m-Y'),
                    $attendance->check_in ?? '-',
                    $attendance->check_out ?? '-',
                    floor($attendance->total_break_seconds / 60),
                    $workHours,
                    $status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
