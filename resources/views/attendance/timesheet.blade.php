<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Timesheet - {{ $employee->name }} ({{ $selectedMonth }})</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-size: 13px; }
        .timesheet-container { max-width: 1050px; margin: 0 auto; background: #fff; padding: 35px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        .table-timesheet th, .table-timesheet td { padding: 7px 10px; font-size: 12px; }
        .signature-line { border-top: 1px solid #333; width: 220px; margin-top: 50px; }

        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .timesheet-container { box-shadow: none; border: none; padding: 0; width: 100% !important; max-width: 100% !important; }
            .table-timesheet { font-size: 10px; }
            .badge { border: 1px solid #999 !important; color: #000 !important; background: transparent !important; }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (No Print) -->
    <div class="bg-dark text-white py-3 mb-4 no-print shadow-sm">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('attendance') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to Attendance
                </a>
                <span class="fs-6 fw-bold">Printable Monthly Timesheet</span>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <form method="GET" action="{{ route('attendance.timesheet') }}" class="d-flex align-items-center gap-2">
                    @if(Auth::user()->isAdmin() && $allEmployees->count())
                        <select name="user_id" class="form-select form-select-sm" style="max-width: 200px;">
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}" {{ $employee->id == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonthRaw }}">
                    <button type="submit" class="btn btn-sm btn-light">Load</button>
                </form>

                <button onclick="window.print()" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Print / Save PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Printable Timesheet Document -->
    <div class="timesheet-container mb-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-buildings-fill fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">ENTERPRISE ATTENDANCE & TIMESHEET</h4>
                        <small class="text-muted">Payroll Verification & Monthly Work Hours Audit</small>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <h5 class="fw-bold text-primary mb-0">{{ $selectedMonth }}</h5>
                <small class="text-muted">Generated on {{ now()->format('d M Y, h:i A') }}</small>
            </div>
        </div>

        <!-- Employee Info Grid -->
        <div class="card border-0 bg-light p-3 mb-4">
            <div class="row g-2">
                <div class="col-md-3">
                    <small class="text-muted d-block">Employee Name:</small>
                    <strong class="text-dark">{{ $employee->name }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Employee ID:</small>
                    <strong class="text-dark">{{ $employee->employee_id ?? 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Department:</small>
                    <strong class="text-dark">{{ $employee->department ?? 'Engineering' }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Designation / Role:</small>
                    <strong class="text-dark">{{ $employee->designation ?? ucfirst($employee->role) }}</strong>
                </div>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="row g-2 text-center mb-4">
            <div class="col">
                <div class="border rounded p-2 bg-white">
                    <small class="text-muted d-block" style="font-size: 11px;">Present Days</small>
                    <span class="fs-5 fw-bold text-success">{{ $totalDaysPresent }}</span>
                </div>
            </div>
            <div class="col">
                <div class="border rounded p-2 bg-white">
                    <small class="text-muted d-block" style="font-size: 11px;">Late Days</small>
                    <span class="fs-5 fw-bold text-warning">{{ $totalDaysLate }}</span>
                </div>
            </div>
            <div class="col">
                <div class="border rounded p-2 bg-white">
                    <small class="text-muted d-block" style="font-size: 11px;">Leaves / WFH</small>
                    <span class="fs-5 fw-bold text-primary">{{ $totalDaysLeave }}</span>
                </div>
            </div>
            <div class="col">
                <div class="border rounded p-2 bg-white">
                    <small class="text-muted d-block" style="font-size: 11px;">Regular Hours</small>
                    <span class="fs-5 fw-bold text-dark">{{ $totalWorkHours }}h</span>
                </div>
            </div>
            <div class="col">
                <div class="border rounded p-2 bg-white">
                    <small class="text-muted d-block" style="font-size: 11px;">Overtime (OT)</small>
                    <span class="fs-5 fw-bold text-success">+{{ $totalOvertimeHours }}h</span>
                </div>
            </div>
            <div class="col">
                <div class="border rounded p-2 bg-white">
                    <small class="text-muted d-block" style="font-size: 11px;">Total Breaks</small>
                    <span class="fs-5 fw-bold text-muted">{{ $totalBreakHours }}h</span>
                </div>
            </div>
            <div class="col">
                <div class="border border-primary border-2 rounded p-2 bg-primary bg-opacity-10">
                    <small class="text-primary fw-bold d-block" style="font-size: 11px;">Net Payable Hours</small>
                    <span class="fs-5 fw-bold text-primary">{{ $netPayableHours }}h</span>
                </div>
            </div>
        </div>

        <!-- Day by Day Timesheet Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped table-hover table-timesheet align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 110px;">Date</th>
                        <th style="width: 95px;">Day</th>
                        <th>Type</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Break</th>
                        <th>Work Hours</th>
                        <th>Overtime</th>
                        <th>Punctuality</th>
                        <th>Early Departure / Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timesheetRows as $row)
                        @php
                            $att = $row['attendance'];
                            $isWeekend = $row['is_weekend'];
                        @endphp
                        <tr class="{{ $isWeekend ? 'table-light text-muted' : '' }}">
                            <td class="fw-semibold">{{ $row['date'] }}</td>
                            <td>{{ $row['day'] }}</td>
                            <td>
                                @if($isWeekend)
                                    <span class="badge bg-light text-dark border">Weekend</span>
                                @elseif($att)
                                    @if($att->attendance_type === 'on_leave')
                                        <span class="badge bg-primary">On Leave</span>
                                    @elseif($att->attendance_type === 'wfh')
                                        <span class="badge bg-info text-dark">WFH</span>
                                    @elseif($att->is_half_day)
                                        <span class="badge bg-dark">Half Day</span>
                                    @else
                                        <span class="badge bg-success">Present</span>
                                    @endif
                                @else
                                    <span class="badge bg-danger">Absent</span>
                                @endif
                            </td>
                            <td>{{ $att && $att->check_in ? $att->check_in : '-' }}</td>
                            <td>{{ $att && $att->check_out ? $att->check_out : '-' }}</td>
                            <td>{{ $att ? $att->getBreakFormatted() : '-' }}</td>
                            <td class="fw-bold">{{ $att ? $att->getWorkHoursFormatted() : '-' }}</td>
                            <td class="text-success fw-semibold">
                                {{ $att && $att->overtime_seconds > 0 ? $att->getOvertimeFormatted() : '-' }}
                            </td>
                            <td>
                                @if($att && $att->status === 'Late')
                                    <span class="badge bg-warning text-dark">Late ({{ $att->late_minutes }}m)</span>
                                @elseif($att && $att->check_in)
                                    <span class="badge bg-success">On Time</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="small">
                                @if($att && $att->early_departure_minutes > 0)
                                    <span class="text-danger fw-semibold">{{ $att->early_departure_minutes }}m early</span>
                                    @if($att->early_departure_reason)
                                        <br><span class="text-muted" style="font-size: 11px;">({{ $att->early_departure_reason }})</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Authorization Signatures Block -->
        <div class="row pt-4 mt-4 border-top">
            <div class="col-md-4 text-center">
                <div class="signature-line mx-auto"></div>
                <small class="fw-bold text-dark d-block mt-2">Employee Signature</small>
                <small class="text-muted">{{ $employee->name }}</small>
            </div>
            <div class="col-md-4 text-center">
                <div class="signature-line mx-auto"></div>
                <small class="fw-bold text-dark d-block mt-2">Supervisor / Manager Signature</small>
                <small class="text-muted">Reviewed & Verified</small>
            </div>
            <div class="col-md-4 text-center">
                <div class="signature-line mx-auto"></div>
                <small class="fw-bold text-dark d-block mt-2">HR & Payroll Department</small>
                <small class="text-muted">Approved for Processing</small>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-3 border-top" style="font-size: 11px;">
            This is an official computer-generated timesheet document from the Attendance & Time Tracking System.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
