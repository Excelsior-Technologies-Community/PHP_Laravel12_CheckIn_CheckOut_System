<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance - Check In & Out System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-box {
            border-radius: 14px;
            transition: .25s ease-in-out;
        }
        .card-box:hover {
            transform: translateY(-4px);
        }
        .stat-number {
            font-size: 28px;
            font-weight: 700;
        }
        .pulse-badge {
            animation: pulse-animation 1.5s infinite;
        }
        @keyframes pulse-animation {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="bg-light">

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('attendance') }}">
                <i class="bi bi-clock-history text-primary"></i>
                <span>Attendance System</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active fw-bold text-primary" href="{{ route('attendance') }}">
                            <i class="bi bi-card-checklist me-1"></i> Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('attendance.calendar') }}">
                            <i class="bi bi-calendar3 me-1"></i> Calendar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('attendance.timesheet') }}">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Timesheet
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('leaves.index') }}">
                            <i class="bi bi-airplane me-1"></i> Leaves & WFH
                        </a>
                    </li>
                    @if(Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-semibold" href="{{ route('admin.leaves.index') }}">
                                <i class="bi bi-shield-lock me-1"></i> Admin Approvals
                            </a>
                        </li>
                    @endif
                </ul>

                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white" href="#" data-bs-toggle="dropdown">
                            <span class="rounded-circle bg-primary text-white d-inline-flex justify-content-center align-items-center" style="width: 32px; height: 32px; font-size: 13px;">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span>{{ Auth::user()->name }}</span>
                            @if(Auth::user()->isAdmin())
                                <span class="badge bg-warning text-dark">Admin</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="px-3 py-2 small text-muted border-bottom">
                                Signed in as <strong>{{ Auth::user()->email }}</strong>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        {{-- Session Flash Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Top Header & Quick Action Buttons -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>Employee Attendance Dashboard</h3>
                <p class="text-muted small mb-0">Track daily check-ins, breaks, overtime hours, and monthly attendance performance.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('attendance.calendar') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-calendar3 me-1"></i> Monthly Calendar
                </a>
                <a href="{{ route('attendance.timesheet') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-printer me-1"></i> Printable Timesheet
                </a>
                <a href="{{ route('leaves.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-airplane me-1"></i> Apply for Leave
                </a>
            </div>
        </div>

        <!-- Overall KPI Cards -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-5 g-3 mb-4">
            <!-- Total Attendance -->
            <div class="col">
                <div class="card card-box border-0 shadow-sm bg-primary text-white h-100 p-3">
                    <div class="card-body p-0">
                        <small class="text-white-50 text-uppercase fw-semibold">Total Days</small>
                        <div class="stat-number my-1">{{ $totalAttendance }}</div>
                        <small class="text-white-50">Completed: {{ $completedAttendance }}</small>
                    </div>
                </div>
            </div>

            <!-- Working Hours -->
            <div class="col">
                <div class="card card-box border-0 shadow-sm bg-success text-white h-100 p-3">
                    <div class="card-body p-0">
                        <small class="text-white-50 text-uppercase fw-semibold">Working Hours</small>
                        <div class="stat-number my-1">{{ $totalWorkingHours }}h</div>
                        <small class="text-white-50">Average shift work</small>
                    </div>
                </div>
            </div>

            <!-- Feature 3: Overtime Hours -->
            <div class="col">
                <div class="card card-box border-0 shadow-sm bg-info text-white h-100 p-3">
                    <div class="card-body p-0">
                        <small class="text-white-50 text-uppercase fw-semibold">Overtime (OT)</small>
                        <div class="stat-number my-1">+{{ $totalOvertimeHours }}h</div>
                        <small class="text-white-50">Extra hours accumulated</small>
                    </div>
                </div>
            </div>

            <!-- Total Breaks -->
            <div class="col">
                <div class="card card-box border-0 shadow-sm bg-secondary text-white h-100 p-3">
                    <div class="card-body p-0">
                        <small class="text-white-50 text-uppercase fw-semibold">Break Minutes</small>
                        <div class="stat-number my-1">{{ $totalBreakMinutes }}m</div>
                        <small class="text-white-50">Rest & lunch intervals</small>
                    </div>
                </div>
            </div>

            <!-- Late Arrivals -->
            <div class="col">
                <div class="card card-box border-0 shadow-sm bg-danger text-white h-100 p-3">
                    <div class="card-body p-0">
                        <small class="text-white-50 text-uppercase fw-semibold">Late Days</small>
                        <div class="stat-number my-1">{{ $lateDays }}</div>
                        <small class="text-white-50">After 9:30 AM shift</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Action Center & Presence Box -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-clock me-2 text-primary"></i>Today's Attendance
                        <span class="text-muted small fw-normal ms-2">({{ now()->format('l, d F Y') }})</span>
                    </h5>
                </div>
                <div>
                    @if($activeBreak)
                        <span class="badge bg-warning text-dark px-3 py-2 pulse-badge">
                            <i class="bi bi-cup-hot-fill me-1"></i> ON BREAK SINCE {{ Carbon\Carbon::parse($activeBreak->break_start)->format('h:i A') }}
                        </span>
                    @elseif($workingToday)
                        <span class="badge bg-success px-3 py-2 pulse-badge">
                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> CURRENTLY WORKING
                        </span>
                    @elseif($attendance && $attendance->check_out)
                        <span class="badge bg-secondary px-3 py-2">
                            <i class="bi bi-check-all me-1"></i> SHIFT COMPLETED
                        </span>
                    @else
                        <span class="badge bg-light text-dark border px-3 py-2">
                            NOT CHECKED IN YET
                        </span>
                    @endif
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Action Buttons Toolbar -->
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <!-- Check In -->
                    <form method="POST" action="{{ route('check-in') }}">
                        @csrf
                        <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow-sm" {{ $attendance && $attendance->check_in ? 'disabled' : '' }}>
                            <i class="bi bi-box-arrow-in-right me-1"></i> Check In
                        </button>
                    </form>

                    <!-- Break Start -->
                    <form method="POST" action="{{ route('break-start') }}">
                        @csrf
                        <button type="submit" class="btn btn-warning px-4 py-2 rounded-pill shadow-sm" {{ (!$attendance || !$attendance->check_in || $attendance->check_out || $activeBreak) ? 'disabled' : '' }}>
                            <i class="bi bi-cup-hot me-1"></i> Start Break
                        </button>
                    </form>

                    <!-- Break End -->
                    <form method="POST" action="{{ route('break-end') }}">
                        @csrf
                        <button type="submit" class="btn btn-info text-white px-4 py-2 rounded-pill shadow-sm" {{ !$activeBreak ? 'disabled' : '' }}>
                            <i class="bi bi-cup me-1"></i> End Break
                        </button>
                    </form>

                    <!-- Feature 3: Check Out Button with Graceful Early Departure or Standard Check Out -->
                    @if($attendance && $attendance->check_in && !$attendance->check_out)
                        <button type="button" class="btn btn-danger px-4 py-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#checkOutModal">
                            <i class="bi bi-box-arrow-right me-1"></i> Check Out
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-pill" disabled>
                            <i class="bi bi-box-arrow-right me-1"></i> Check Out
                        </button>
                    @endif
                </div>

                <!-- Today's Attendance Details & Breaks Table -->
                @if($attendance)
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border bg-light h-100">
                                <div class="card-header bg-white py-2 fw-semibold">
                                    <i class="bi bi-info-circle text-primary me-1"></i> Punch Timestamps
                                </div>
                                <div class="card-body p-3">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 40%;">Check In Time:</td>
                                            <td class="fw-bold">{{ $attendance->check_in ? Carbon\Carbon::parse($attendance->check_in)->format('h:i:s A') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Check Out Time:</td>
                                            <td class="fw-bold">{{ $attendance->check_out ? Carbon\Carbon::parse($attendance->check_out)->format('h:i:s A') : 'In progress...' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Break Duration:</td>
                                            <td class="fw-bold">{{ floor($attendance->total_break_seconds / 60) }} minutes</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Net Work Hours:</td>
                                            <td class="fw-bold text-primary">{{ $attendance->getWorkHoursFormatted() }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Overtime (OT):</td>
                                            <td class="fw-bold text-success">{{ $attendance->getOvertimeFormatted() }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status:</td>
                                            <td>{!! $attendance->getStatusBadge() !!}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border bg-light h-100">
                                <div class="card-header bg-white py-2 fw-semibold">
                                    <i class="bi bi-cup-hot text-warning me-1"></i> Today's Break Log ({{ $attendance->breaks->count() }})
                                </div>
                                <div class="card-body p-3">
                                    @if($attendance->breaks->isEmpty())
                                        <p class="text-muted small mb-0 py-3 text-center">No breaks taken today yet.</p>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                <thead class="table-light small">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Start</th>
                                                        <th>End</th>
                                                        <th>Duration</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($attendance->breaks as $idx => $brk)
                                                        <tr>
                                                            <td>{{ $idx + 1 }}</td>
                                                            <td>{{ Carbon\Carbon::parse($brk->break_start)->format('h:i A') }}</td>
                                                            <td>{{ $brk->break_end ? Carbon\Carbon::parse($brk->break_end)->format('h:i A') : 'Active' }}</td>
                                                            <td>{{ floor($brk->break_seconds / 60) }} mins</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-light border py-3 text-muted text-center mb-0">
                        <i class="bi bi-info-circle me-1"></i> You haven't punched in today. Click <strong>Check In</strong> to begin your shift.
                    </div>
                @endif
            </div>
        </div>

        <!-- Check Out Modal (Allows Early Departure Note if under 8 hours) -->
        <div class="modal fade" id="checkOutModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-box-arrow-right me-1"></i> Confirm Shift Check-Out
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('check-out') }}">
                        @csrf
                        <div class="modal-body p-4">
                            <p class="mb-3">Are you ready to clock out for today? Any running breaks will be finalized automatically.</p>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Early Departure Reason (Optional):</label>
                                <textarea name="early_departure_reason" rows="2" class="form-control form-control-sm" placeholder="If you are checking out before completing the 8-hour shift, mention your reason here (e.g. Doctor appointment, client site visit)..."></textarea>
                                <div class="form-text">Work under 8 hours will automatically be recorded with early departure minutes or half day.</div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">
                                <i class="bi bi-box-arrow-right me-1"></i> Confirm Check-Out
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Monthly Performance Report Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-dark text-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>Monthly Attendance Performance
                    <span class="badge bg-primary ms-2">{{ $selectedMonth }}</span>
                </h5>

                <form method="GET" action="{{ route('attendance') }}" class="d-flex align-items-center gap-2">
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonthRaw }}">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('attendance') }}" class="btn btn-sm btn-outline-light">Reset</a>
                </form>
            </div>

            <div class="card-body p-4">
                <div class="row row-cols-2 row-cols-md-4 row-cols-lg-7 g-3 text-center mb-4">
                    <div class="col">
                        <div class="p-3 border rounded bg-white shadow-sm">
                            <small class="text-muted d-block" style="font-size: 11px;">Present Days</small>
                            <h4 class="fw-bold text-success mb-0">{{ $monthlyPresentDays }}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 border rounded bg-white shadow-sm">
                            <small class="text-muted d-block" style="font-size: 11px;">Late Days</small>
                            <h4 class="fw-bold text-warning mb-0">{{ $monthlyLateDays }}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 border rounded bg-white shadow-sm">
                            <small class="text-muted d-block" style="font-size: 11px;">Overtime Days</small>
                            <h4 class="fw-bold text-info mb-0">{{ $monthlyOvertimeDays }}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 border rounded bg-white shadow-sm">
                            <small class="text-muted d-block" style="font-size: 11px;">Early Leaves</small>
                            <h4 class="fw-bold text-danger mb-0">{{ $monthlyEarlyDepartureDays }}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 border rounded bg-white shadow-sm">
                            <small class="text-muted d-block" style="font-size: 11px;">Leaves / WFH</small>
                            <h4 class="fw-bold text-primary mb-0">{{ $monthlyLeaveDays + $monthlyWfhDays }}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 border rounded bg-white shadow-sm">
                            <small class="text-muted d-block" style="font-size: 11px;">Overtime Hours</small>
                            <h4 class="fw-bold text-success mb-0">+{{ $monthlyOvertimeHours }}h</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 border rounded bg-primary bg-opacity-10 border-primary shadow-sm">
                            <small class="text-primary fw-bold d-block" style="font-size: 11px;">Payable Hours</small>
                            <h4 class="fw-bold text-primary mb-0">{{ $monthlyNetPayableHours }}h</h4>
                        </div>
                    </div>
                </div>

                <!-- Punctuality / Attendance Progress Bar -->
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="fw-semibold">Monthly Attendance Score</span>
                        <span class="fw-bold {{ $attendancePercentage >= 85 ? 'text-success' : ($attendancePercentage >= 70 ? 'text-warning' : 'text-danger') }}">{{ $attendancePercentage }}%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar {{ $attendancePercentage >= 85 ? 'bg-success' : ($attendancePercentage >= 70 ? 'bg-warning' : 'bg-danger') }}"
                             role="progressbar" style="width: {{ min(100, $attendancePercentage) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance History Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-clock-history text-primary me-2"></i>Attendance Log History
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('attendance.calendar') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-calendar3 me-1"></i> Calendar View
                    </a>
                    <a href="{{ route('attendance.timesheet') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                        <i class="bi bi-printer me-1"></i> Print Timesheet
                    </a>
                    <a href="{{ route('attendance.export', request()->all()) }}" class="btn btn-success btn-sm rounded-pill px-3">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </a>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="card-body p-3 bg-light border-bottom">
                <form method="GET" action="{{ route('attendance') }}" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="date" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by date">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">-- Filter by Status / Type --</option>
                            <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Currently Working</option>
                            <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out (Completed)</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late Arrival Only</option>
                            <option value="overtime" {{ request('status') == 'overtime' ? 'selected' : '' }}>Overtime Shifts (> 8h)</option>
                            <option value="early_departure" {{ request('status') == 'early_departure' ? 'selected' : '' }}>Early Departures (< 8h)</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Days (< 4h)</option>
                            <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>Leaves & WFH</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                        <a href="{{ route('attendance') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                @if($attendanceHistory->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <h6>No attendance records found</h6>
                        <small>Try clearing filters or search query.</small>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Type</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Break</th>
                                    <th>Work Duration</th>
                                    <th>Overtime (OT)</th>
                                    <th>Punctuality</th>
                                    <th>Early Departure</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendanceHistory as $record)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            {{ $record->date->format('d M Y') }}
                                            <small class="text-muted d-block fw-normal">{{ $record->date->format('l') }}</small>
                                        </td>
                                        <td>
                                            @if($record->attendance_type === 'on_leave')
                                                <span class="badge bg-primary">On Leave</span>
                                            @elseif($record->attendance_type === 'wfh')
                                                <span class="badge bg-info text-dark">WFH</span>
                                            @elseif($record->is_half_day)
                                                <span class="badge bg-dark">Half Day</span>
                                            @else
                                                <span class="badge bg-light text-dark border">Present</span>
                                            @endif
                                        </td>
                                        <td>{{ $record->check_in ? Carbon\Carbon::parse($record->check_in)->format('h:i A') : '-' }}</td>
                                        <td>{{ $record->check_out ? Carbon\Carbon::parse($record->check_out)->format('h:i A') : ($record->check_in ? 'Working...' : '-') }}</td>
                                        <td>{{ floor($record->total_break_seconds / 60) }}m</td>
                                        <td class="fw-bold">{{ $record->getWorkHoursFormatted() }}</td>
                                        <td>
                                            @if($record->overtime_seconds > 0)
                                                <span class="badge bg-success">{{ $record->getOvertimeFormatted() }}</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>{!! $record->getStatusBadge() !!}</td>
                                        <td>
                                            @if($record->early_departure_minutes > 0)
                                                <span class="badge bg-danger">{{ $record->early_departure_minutes }}m early</span>
                                                @if($record->early_departure_reason)
                                                    <i class="bi bi-info-circle text-muted ms-1" title="{{ $record->early_departure_reason }}"></i>
                                                @endif
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($attendanceHistory->hasPages())
                        <div class="p-3 border-top d-flex justify-content-end">
                            {{ $attendanceHistory->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>