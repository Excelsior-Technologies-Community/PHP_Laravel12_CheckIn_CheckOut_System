<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Leave Approvals - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-box { border-radius: 14px; transition: .25s ease-in-out; }
        .card-box:hover { transform: translateY(-3px); }
        .stat-number { font-size: 28px; font-weight: 700; }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('attendance') }}">
                <i class="bi bi-shield-lock-fill text-warning"></i>
                <span>Admin Attendance Portal</span>
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
                        <a class="nav-link" href="{{ route('leaves.index') }}">
                            <i class="bi bi-airplane me-1"></i> My Leaves
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-bold text-warning" href="{{ route('admin.leaves.index') }}">
                            <i class="bi bi-check2-circle me-1"></i> Manage Approvals
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2">
                        <a href="{{ route('admin.attendance.export-all') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Bulk Export Staff CSV
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white" href="#" data-bs-toggle="dropdown">
                            <span class="rounded-circle bg-warning text-dark fw-bold d-inline-flex justify-content-center align-items-center" style="width: 32px; height: 32px; font-size: 13px;">
                                A
                            </span>
                            <span>{{ Auth::user()->name }}</span>
                            <span class="badge bg-warning text-dark">Admin</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="px-3 py-2 small text-muted border-bottom">
                                Admin Account: <strong>{{ Auth::user()->email }}</strong>
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
                <h3 class="fw-bold mb-1"><i class="bi bi-shield-check text-warning me-2"></i>Staff Leave & WFH Approval Workflow</h3>
                <p class="text-muted small mb-0">Review pending employee leave applications. Approved leaves automatically synchronize with the attendance calendar.</p>
            </div>
            <a href="{{ route('admin.attendance.export-all') }}" class="btn btn-outline-dark rounded-pill px-4 shadow-sm">
                <i class="bi bi-download me-1"></i> Export Monthly Timesheets
            </a>
        </div>

        <!-- Quick Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-box border-0 shadow-sm bg-white p-3 border-start border-warning border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Pending Approvals</span>
                            <div class="stat-number text-warning mt-1">{{ $pendingCount }}</div>
                            <small class="text-muted">Awaiting your review</small>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3">
                            <i class="bi bi-hourglass-split fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-box border-0 shadow-sm bg-white p-3 border-start border-success border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Approved Leaves</span>
                            <div class="stat-number text-success mt-1">{{ $approvedCount }}</div>
                            <small class="text-muted">Synchronized with attendance</small>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3">
                            <i class="bi bi-check2-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-box border-0 shadow-sm bg-white p-3 border-start border-danger border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Rejected Requests</span>
                            <div class="stat-number text-danger mt-1">{{ $rejectedCount }}</div>
                            <small class="text-muted">Declined applications</small>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3">
                            <i class="bi bi-x-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.leaves.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">-- All Employees --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->employee_id ?? 'EMP-' . $emp->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="type" class="form-select form-select-sm">
                            <option value="">-- All Types --</option>
                            <option value="casual" {{ request('type') == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                            <option value="sick" {{ request('type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="emergency" {{ request('type') == 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                            <option value="wfh" {{ request('type') == 'wfh' ? 'selected' : '' }}>Work From Home (WFH)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">-- All Statuses --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Only</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved Only</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected Only</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.leaves.index') }}" class="btn btn-sm btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-list-task text-primary me-2"></i>Employee Applications</h6>
                <span class="badge bg-light text-dark border">{{ $leaves->total() }} Applications</span>
            </div>
            <div class="card-body p-0">
                @if($leaves->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted fs-1 mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark">No leave applications found</h6>
                        <p class="text-muted small">No employee applications match the selected filter criteria.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Employee</th>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Duration</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Actioned By / Remarks</th>
                                    <th class="text-end pe-4">Decision</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaves as $leave)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $leave->user->name }}</div>
                                            <small class="text-muted">{{ $leave->user->email }} • {{ $leave->user->department ?? 'General' }}</small>
                                        </td>
                                        <td>{!! $leave->getTypeBadge() !!}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $leave->start_date->format('d M Y') }}</div>
                                            @if(!$leave->start_date->equalTo($leave->end_date))
                                                <small class="text-muted">to {{ $leave->end_date->format('d M Y') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $leave->days_count }} day{{ $leave->days_count > 1 ? 's' : '' }}
                                            </span>
                                            @if($leave->is_half_day)
                                                <span class="badge bg-secondary ms-1">Half Day</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 220px;" title="{{ $leave->reason }}">
                                                {{ $leave->reason }}
                                            </span>
                                        </td>
                                        <td>{!! $leave->getStatusBadge() !!}</td>
                                        <td>
                                            @if($leave->admin_remarks)
                                                <small class="d-block text-dark"><strong>Remark:</strong> {{ $leave->admin_remarks }}</small>
                                            @endif
                                            @if($leave->approver)
                                                <small class="text-muted">By {{ $leave->approver->name }} on {{ $leave->actioned_at ? $leave->actioned_at->format('d M') : '' }}</small>
                                            @elseif(!$leave->admin_remarks)
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($leave->isPending())
                                                <div class="d-inline-flex gap-2">
                                                    <!-- Approve Button -->
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#approveModal{{ $leave->id }}">
                                                        <i class="bi bi-check-lg me-1"></i> Approve
                                                    </button>
                                                    <!-- Reject Button -->
                                                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}">
                                                        <i class="bi bi-x-lg me-1"></i> Reject
                                                    </button>
                                                </div>

                                                <!-- Approve Modal -->
                                                <div class="modal fade text-start" id="approveModal{{ $leave->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow">
                                                            <div class="modal-header bg-success text-white">
                                                                <h6 class="modal-title fw-bold">
                                                                    <i class="bi bi-check-circle me-1"></i> Approve Leave Request #{{ $leave->id }}
                                                                </h6>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST" action="{{ route('admin.leaves.approve', $leave) }}">
                                                                @csrf
                                                                <div class="modal-body p-4">
                                                                    <p class="mb-2">Are you sure you want to approve this <strong>{{ $leave->getTypeLabel() }}</strong> for <strong>{{ $leave->user->name }}</strong>?</p>
                                                                    <p class="text-muted small mb-3">Duration: <strong>{{ $leave->days_count }} day(s)</strong> from {{ $leave->start_date->format('d M Y') }} to {{ $leave->end_date->format('d M Y') }}.</p>
                                                                    
                                                                    <label class="form-label small fw-semibold">Optional Approval Remarks</label>
                                                                    <input type="text" name="admin_remarks" class="form-control form-control-sm" placeholder="e.g. Approved. Have a good break.">
                                                                    <div class="form-text">Approved days will automatically synchronize into attendance as On Leave / WFH.</div>
                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 shadow-sm">
                                                                        Confirm & Approve
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Reject Modal -->
                                                <div class="modal fade text-start" id="rejectModal{{ $leave->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h6 class="modal-title fw-bold">
                                                                    <i class="bi bi-x-circle me-1"></i> Reject Leave Request #{{ $leave->id }}
                                                                </h6>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST" action="{{ route('admin.leaves.reject', $leave) }}">
                                                                @csrf
                                                                <div class="modal-body p-4">
                                                                    <p class="mb-2">Rejecting application for <strong>{{ $leave->user->name }}</strong>.</p>
                                                                    <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                                                                    <textarea name="admin_remarks" rows="2" class="form-control form-control-sm" placeholder="Please provide reason for rejection..." required></textarea>
                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 shadow-sm">
                                                                        Confirm Rejection
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-light text-muted border">Completed</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
