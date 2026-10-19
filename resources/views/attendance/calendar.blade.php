<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Calendar - {{ $selectedMonth }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 12px;
        }
        @media (max-width: 992px) {
            .calendar-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .calendar-grid {
                grid-template-columns: 1fr;
            }
        }
        .calendar-cell {
            background: #fff;
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #e9ecef;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all .2s ease-in-out;
            cursor: pointer;
            position: relative;
        }
        .calendar-cell:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.08);
            border-color: #0d6efd;
        }
        .calendar-cell.weekend {
            background: #f8fafc;
            border-style: dashed;
            opacity: 0.85;
        }
        .calendar-cell.today {
            border: 2px solid #0d6efd;
            box-shadow: 0 0 10px rgba(13,110,253,0.25);
        }
        .day-header {
            font-weight: 700;
            font-size: 16px;
        }
        .stat-badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 20px;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('attendance') }}">
                <i class="bi bi-clock-history text-primary"></i>
                <span>Attendance System</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('attendance') }}">
                            <i class="bi bi-card-checklist me-1"></i> Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-bold text-primary" href="{{ route('attendance.calendar') }}">
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

        <!-- Top Header & Navigation -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('attendance.calendar', ['month' => $prevMonth]) }}" class="btn btn-outline-secondary btn-sm rounded-circle" title="Previous Month">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <h3 class="fw-bold mb-0">
                        <i class="bi bi-calendar-month text-primary me-2"></i>{{ $selectedMonth }}
                    </h3>
                    <a href="{{ route('attendance.calendar', ['month' => $nextMonth]) }}" class="btn btn-outline-secondary btn-sm rounded-circle" title="Next Month">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <form method="GET" action="{{ route('attendance.calendar') }}" class="d-flex gap-2">
                        <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonthRaw }}">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Go</button>
                    </form>
                    <a href="{{ route('attendance.timesheet', ['month' => $selectedMonthRaw]) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                        <i class="bi bi-printer me-1"></i> Print Timesheet
                    </a>
                    <a href="{{ route('attendance') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Legend & Month Stats -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <!-- Legend -->
                    <div class="d-flex flex-wrap align-items-center gap-3 small">
                        <span class="fw-bold text-muted">Legend:</span>
                        <span class="d-inline-flex align-items-center gap-1"><span class="badge bg-success">&nbsp;</span> Present (On Time)</span>
                        <span class="d-inline-flex align-items-center gap-1"><span class="badge bg-warning text-dark">&nbsp;</span> Late Arrival</span>
                        <span class="d-inline-flex align-items-center gap-1"><span class="badge bg-primary">&nbsp;</span> On Leave</span>
                        <span class="d-inline-flex align-items-center gap-1"><span class="badge bg-info text-dark">&nbsp;</span> WFH</span>
                        <span class="d-inline-flex align-items-center gap-1"><span class="badge bg-dark">&nbsp;</span> Half Day</span>
                        <span class="d-inline-flex align-items-center gap-1"><span class="badge bg-danger">&nbsp;</span> Absent</span>
                        <span class="d-inline-flex align-items-center gap-1"><span class="badge bg-light text-dark border">&nbsp;</span> Weekend</span>
                    </div>

                    <!-- Summary Counters -->
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-success px-3 py-2">{{ $presentCount }} Present</span>
                        <span class="badge bg-warning text-dark px-3 py-2">{{ $lateCount }} Late</span>
                        <span class="badge bg-primary px-3 py-2">{{ $leaveCount }} Leaves</span>
                        <span class="badge bg-dark px-3 py-2">{{ $halfDayCount }} Half Days</span>
                        <span class="badge bg-danger px-3 py-2">{{ $absentCount }} Absent</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Calendar Grid -->
        <div class="calendar-grid mb-4">
            @foreach($calendarDays as $day)
                @php
                    $att = $day['attendance'];
                    $hasAtt = !is_null($att);
                    $breaksJson = $hasAtt ? $att->breaks->toJson() : '[]';
                @endphp
                <div class="calendar-cell {{ $day['is_weekend'] ? 'weekend' : '' }} {{ $day['is_today'] ? 'today' : '' }}"
                     onclick="openDayModal(
                         '{{ Carbon\Carbon::parse($day['date'])->format('l, d F Y') }}',
                         '{{ $day['status_label'] }}',
                         '{{ $day['badge_color'] }}',
                         '{{ $hasAtt && $att->check_in ? $att->check_in : '-' }}',
                         '{{ $hasAtt && $att->check_out ? $att->check_out : '-' }}',
                         '{{ $hasAtt ? $att->getWorkHoursFormatted() : '-' }}',
                         '{{ $hasAtt ? $att->getOvertimeFormatted() : '0m' }}',
                         '{{ $hasAtt ? $att->getBreakFormatted() : '0m' }}',
                         '{{ $hasAtt ? ($att->late_minutes ?? 0) : 0 }}',
                         '{{ $hasAtt ? ($att->early_departure_minutes ?? 0) : 0 }}',
                         '{{ $hasAtt ? addslashes($att->early_departure_reason ?? '') : '' }}',
                         {{ $breaksJson }}
                     )">

                    <!-- Cell Header -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="day-header text-dark">{{ $day['day_number'] }}</span>
                            <small class="text-muted ms-1">{{ $day['day_name'] }}</small>
                        </div>
                        @if($day['is_today'])
                            <span class="badge bg-primary px-2" style="font-size: 10px;">TODAY</span>
                        @endif
                    </div>

                    <!-- Cell Body -->
                    <div class="my-auto">
                        <span class="badge bg-{{ $day['badge_color'] }} stat-badge mb-1 d-inline-block">
                            {{ $day['status_label'] }}
                        </span>

                        @if($hasAtt && $att->check_in)
                            <div class="small text-dark font-monospace" style="font-size: 11px;">
                                <i class="bi bi-box-arrow-in-right text-success"></i> {{ Carbon\Carbon::parse($att->check_in)->format('h:i A') }}
                                @if($att->check_out)
                                    - <i class="bi bi-box-arrow-right text-danger"></i> {{ Carbon\Carbon::parse($att->check_out)->format('h:i A') }}
                                @endif
                            </div>
                            @if($att->total_work_seconds)
                                <div class="small fw-semibold text-muted" style="font-size: 11px;">
                                    <i class="bi bi-stopwatch me-1"></i>{{ $att->getWorkHoursFormatted() }}
                                    @if($att->overtime_seconds > 0)
                                        <span class="text-success ms-1">({{ $att->getOvertimeFormatted() }})</span>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Cell Footer / Click hint -->
                    <div class="mt-2 text-end text-muted small" style="font-size: 10px;">
                        <span>Click for details <i class="bi bi-chevron-right"></i></span>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <!-- Day Details Modal -->
    <div class="modal fade" id="dayDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalDayDate">
                        Day Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted fw-semibold">Attendance Status:</span>
                        <span id="modalDayBadge" class="badge px-3 py-2"></span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded border">
                                <small class="text-muted d-block mb-1"><i class="bi bi-box-arrow-in-right text-success me-1"></i>Check-In Time</small>
                                <span class="fw-bold fs-6" id="modalCheckIn">-</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded border">
                                <small class="text-muted d-block mb-1"><i class="bi bi-box-arrow-right text-danger me-1"></i>Check-Out Time</small>
                                <span class="fw-bold fs-6" id="modalCheckOut">-</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded border">
                                <small class="text-muted d-block mb-1"><i class="bi bi-clock me-1 text-primary"></i>Working Duration</small>
                                <span class="fw-bold fs-6" id="modalWorkDuration">-</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded border">
                                <small class="text-muted d-block mb-1"><i class="bi bi-lightning-charge me-1 text-success"></i>Overtime Recorded</small>
                                <span class="fw-bold fs-6 text-success" id="modalOvertime">0m</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded border">
                                <small class="text-muted d-block mb-1"><i class="bi bi-cup-hot me-1 text-warning"></i>Total Breaks</small>
                                <span class="fw-bold fs-6" id="modalTotalBreak">0m</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded border">
                                <small class="text-muted d-block mb-1"><i class="bi bi-hourglass-bottom me-1 text-danger"></i>Early Departure</small>
                                <span class="fw-bold fs-6 text-danger" id="modalEarlyDeparture">0m</span>
                            </div>
                        </div>
                    </div>

                    <!-- Late or Early Reason Notes -->
                    <div id="modalNotesSection" class="alert alert-warning py-2 small d-none mb-3">
                        <i class="bi bi-info-circle me-1"></i> <span id="modalNotesText"></span>
                    </div>

                    <!-- Break Intervals List -->
                    <div class="card border-0 bg-light">
                        <div class="card-body p-3">
                            <h6 class="fw-bold small mb-2"><i class="bi bi-list-nested me-1"></i>Break Periods Log</h6>
                            <div id="modalBreaksList" class="small text-muted">
                                No breaks recorded for this day.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const dayModal = new bootstrap.Modal(document.getElementById('dayDetailModal'));

        function openDayModal(dateStr, statusLabel, badgeColor, checkIn, checkOut, workDuration, overtime, totalBreak, lateMins, earlyMins, earlyReason, breaks) {
            document.getElementById('modalDayDate').innerText = dateStr;
            const badgeEl = document.getElementById('modalDayBadge');
            badgeEl.innerText = statusLabel;
            badgeEl.className = 'badge px-3 py-2 bg-' + badgeColor;

            document.getElementById('modalCheckIn').innerText = checkIn !== '-' ? checkIn : 'Not Checked In';
            document.getElementById('modalCheckOut').innerText = checkOut !== '-' ? checkOut : 'Not Checked Out';
            document.getElementById('modalWorkDuration').innerText = workDuration;
            document.getElementById('modalOvertime').innerText = overtime;
            document.getElementById('modalTotalBreak').innerText = totalBreak;
            document.getElementById('modalEarlyDeparture').innerText = earlyMins > 0 ? earlyMins + ' minutes' : 'None';

            const notesSection = document.getElementById('modalNotesSection');
            const notesText = document.getElementById('modalNotesText');
            let notes = [];

            if (parseInt(lateMins) > 0) {
                notes.push('Late arrival by ' + lateMins + ' minutes.');
            }
            if (parseInt(earlyMins) > 0) {
                notes.push('Departed ' + earlyMins + ' minutes early.' + (earlyReason ? ' Reason: "' + earlyReason + '"' : ''));
            }

            if (notes.length > 0) {
                notesSection.classList.remove('d-none');
                notesText.innerText = notes.join(' ');
            } else {
                notesSection.classList.add('d-none');
            }

            // Populate breaks
            const breaksContainer = document.getElementById('modalBreaksList');
            if (breaks && breaks.length > 0) {
                let html = '<ul class="list-unstyled mb-0">';
                breaks.forEach((b, index) => {
                    const mins = Math.floor(b.break_seconds / 60);
                    html += `<li class="py-1 border-bottom d-flex justify-content-between">
                        <span>Break #${index + 1}: ${b.break_start} - ${b.break_end ? b.break_end : 'Running'}</span>
                        <span class="fw-semibold text-dark">${mins} mins</span>
                    </li>`;
                });
                html += '</ul>';
                breaksContainer.innerHTML = html;
            } else {
                breaksContainer.innerHTML = '<em>No breaks recorded on this date.</em>';
            }

            dayModal.show();
        }
    </script>
</body>
</html>
