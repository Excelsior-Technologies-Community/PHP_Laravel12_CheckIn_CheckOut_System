<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-box { border-radius: 14px; transition: .25s ease-in-out; }
        .card-box:hover { transform: translateY(-4px); }
        .stat-number { font-size: 28px; font-weight: 700; }
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
                        <a class="nav-link active fw-bold text-primary" href="{{ route('leaves.index') }}">
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

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-airplane-engines text-primary me-2"></i>Leave & WFH Management</h3>
                <p class="text-muted small mb-0">Track your annual leave quotas, apply for leaves, and monitor manager approval status.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                    <i class="bi bi-plus-circle me-1"></i> Apply for Leave / WFH
                </button>
                <a href="{{ route('attendance.calendar') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
                    <i class="bi bi-calendar3 me-1"></i> View Calendar
                </a>
            </div>
        </div>

        <!-- Quota Balance Cards -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
            <!-- Casual Leave -->
            <div class="col">
                <div class="card card-box border-0 shadow-sm bg-white h-100 p-3 border-start border-primary border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Casual Leave</span>
                            <div class="stat-number text-primary mt-1">{{ $quotas['casual']['remaining'] }} <small class="fs-6 text-muted">left</small></div>
                            <small class="text-muted">Used: {{ $quotas['casual']['used'] }} / {{ $quotas['casual']['total'] }} days</small>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3">
                            <i class="bi bi-briefcase fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sick Leave -->
            <div class="col">
                <div class="card card-box border-0 shadow-sm bg-white h-100 p-3 border-start border-danger border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Sick Leave</span>
                            <div class="stat-number text-danger mt-1">{{ $quotas['sick']['remaining'] }} <small class="fs-6 text-muted">left</small></div>
                            <small class="text-muted">Used: {{ $quotas['sick']['used'] }} / {{ $quotas['sick']['total'] }} days</small>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3">
                            <i class="bi bi-heart-pulse fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Leave -->
            <div class="col">
                <div class="card card-box border-0 shadow-sm bg-white h-100 p-3 border-start border-dark border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Emergency Leave</span>
                            <div class="stat-number text-dark mt-1">{{ $quotas['emergency']['remaining'] }} <small class="fs-6 text-muted">left</small></div>
                            <small class="text-muted">Used: {{ $quotas['emergency']['used'] }} / {{ $quotas['emergency']['total'] }} days</small>
                        </div>
                        <div class="rounded-circle bg-dark bg-opacity-10 text-dark p-3">
                            <i class="bi bi-exclamation-octagon fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WFH Quota -->
            <div class="col">
                <div class="card card-box border-0 shadow-sm bg-white h-100 p-3 border-start border-info border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Work From Home (WFH)</span>
                            <div class="stat-number text-info mt-1">{{ $quotas['wfh']['remaining'] }} <small class="fs-6 text-muted">left</small></div>
                            <small class="text-muted">Used: {{ $quotas['wfh']['used'] }} / {{ $quotas['wfh']['total'] }} days</small>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 text-info p-3">
                            <i class="bi bi-house-door fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('leaves.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <select name="type" class="form-select form-select-sm">
                            <option value="">-- All Leave Types --</option>
                            <option value="casual" {{ request('type') == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                            <option value="sick" {{ request('type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="emergency" {{ request('type') == 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                            <option value="wfh" {{ request('type') == 'wfh' ? 'selected' : '' }}>Work From Home (WFH)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">-- All Statuses --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="year" class="form-select form-select-sm">
                            @for($y = now()->year + 1; $y >= now()->year - 2; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Leaves History Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>My Leave History</h6>
                <span class="badge bg-light text-dark border">{{ $leaves->total() }} Total Applications</span>
            </div>
            <div class="card-body p-0">
                @if($leaves->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x text-muted fs-1 mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark">No leave applications found</h6>
                        <p class="text-muted small">You haven't submitted any leave applications matching the filter criteria.</p>
                        <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                            <i class="bi bi-plus-circle me-1"></i> Apply Now
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Duration</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Admin Remarks</th>
                                    <th>Applied On</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaves as $leave)
                                    <tr>
                                        <td class="ps-4 fw-bold text-secondary">#{{ $leave->id }}</td>
                                        <td>{!! $leave->getTypeBadge() !!}</td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $leave->start_date->format('d M Y') }}</div>
                                            @if(!$leave->start_date->equalTo($leave->end_date))
                                                <small class="text-muted">to {{ $leave->end_date->format('d M Y') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $leave->days_count }} day{{ $leave->days_count > 1 ? 's' : '' }}
                                            </span>
                                            @if($leave->is_half_day)
                                                <span class="badge bg-secondary ms-1">Half Day ({{ ucfirst(str_replace('_', ' ', $leave->half_day_type)) }})</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $leave->reason }}">
                                                {{ $leave->reason }}
                                            </span>
                                        </td>
                                        <td>{!! $leave->getStatusBadge() !!}</td>
                                        <td>
                                            <small class="text-muted">{{ $leave->admin_remarks ?? '-' }}</small>
                                        </td>
                                        <td class="small text-muted">{{ $leave->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="text-end pe-4">
                                            @if($leave->isPending())
                                                <form action="{{ route('leaves.destroy', $leave) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Cancel this leave application?')">
                                                        <i class="bi bi-trash me-1"></i> Cancel
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">No action</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($leaves->hasPages())
                        <div class="p-3 border-top d-flex justify-content-end">
                            {{ $leaves->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>

    <!-- Apply Leave Modal -->
    <div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-labelledby="applyLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="applyLeaveModalLabel">
                        <i class="bi bi-calendar-plus me-2"></i>Apply for Leave / Work From Home
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('leaves.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
                            <select name="leave_type" id="modalLeaveType" class="form-select" required>
                                <option value="casual">Casual Leave (Remaining: {{ $quotas['casual']['remaining'] }} days)</option>
                                <option value="sick">Sick Leave (Remaining: {{ $quotas['sick']['remaining'] }} days)</option>
                                <option value="emergency">Emergency Leave (Remaining: {{ $quotas['emergency']['remaining'] }} days)</option>
                                <option value="wfh">Work From Home (Remaining: {{ $quotas['wfh']['remaining'] }} days)</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="modalStartDate" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-6" id="endDateWrapper">
                                <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="modalEndDate" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Half Day Option -->
                        <div class="mb-3 p-3 bg-light rounded border">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_half_day" value="1" id="halfDayToggle">
                                <label class="form-check-label fw-semibold" for="halfDayToggle">Half Day Leave (0.5 Day)</label>
                            </div>

                            <div id="halfDayTypeOptions" class="d-none mt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="half_day_type" id="firstHalf" value="first_half" checked>
                                    <label class="form-check-label small" for="firstHalf">First Half (Morning)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="half_day_type" id="secondHalf" value="second_half">
                                    <label class="form-check-label small" for="secondHalf">Second Half (Afternoon)</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reason for Absence <span class="text-danger">*</span></label>
                            <textarea name="reason" rows="3" class="form-control" placeholder="Please explain the reason for your leave or WFH..." required minlength="5"></textarea>
                        </div>

                        <div class="alert alert-info py-2 small mb-0">
                            <i class="bi bi-info-circle me-1"></i> Weekends (Saturday & Sunday) are automatically excluded from leave duration calculation.
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-send-check me-1"></i> Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const halfDayToggle = document.getElementById('halfDayToggle');
        const halfDayOptions = document.getElementById('halfDayTypeOptions');
        const endDateWrapper = document.getElementById('endDateWrapper');
        const startDateInput = document.getElementById('modalStartDate');
        const endDateInput = document.getElementById('modalEndDate');

        halfDayToggle.addEventListener('change', function() {
            if (this.checked) {
                halfDayOptions.classList.remove('d-none');
                endDateInput.value = startDateInput.value;
                endDateWrapper.style.opacity = '0.5';
                endDateInput.readOnly = true;
            } else {
                halfDayOptions.classList.add('d-none');
                endDateWrapper.style.opacity = '1';
                endDateInput.readOnly = false;
            }
        });

        startDateInput.addEventListener('change', function() {
            if (halfDayToggle.checked) {
                endDateInput.value = this.value;
            } else if (new Date(endDateInput.value) < new Date(this.value)) {
                endDateInput.value = this.value;
            }
            endDateInput.min = this.value;
        });
    </script>
</body>
</html>
