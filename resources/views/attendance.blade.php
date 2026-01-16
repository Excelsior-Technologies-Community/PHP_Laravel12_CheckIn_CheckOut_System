<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">Attendance System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/attendance">Attendance</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Today's Attendance (Indian Time: {{ now()->format('Y-m-d H:i:s') }})</h4>
                    </div>

                    <div class="card-body">
                        <!-- Display Messages -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap gap-3 mb-4">
                            <form method="POST" action="{{ route('check-in') }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg">Check In</button>
                            </form>

                            <form method="POST" action="{{ route('break-start') }}">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-lg">Break Start</button>
                            </form>

                            <form method="POST" action="{{ route('break-end') }}">
                                @csrf
                                <button type="submit" class="btn btn-info btn-lg">Break End</button>
                            </form>

                            <form method="POST" action="{{ route('check-out') }}">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-lg">Check Out</button>
                            </form>
                        </div>

                        <hr>

                        <!-- Attendance Details -->
                        @if($attendance)
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Attendance Summary</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Date</th>
                                            <td>{{ $attendance->date->format('d-m-Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Check In Time</th>
                                            <td>{{ $attendance->check_in ?? 'Not checked in' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Check Out Time</th>
                                            <td>{{ $attendance->check_out ?? 'Not checked out' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Breaks</th>
                                            <td>
                                                {{ floor($attendance->total_break_seconds / 60) }} minutes
                                                ({{ $attendance->breaks->count() }} breaks)
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Working Time</th>
                                            <td>
                                                @if($attendance->total_work_seconds)
                                                    {{ floor($attendance->total_work_seconds / 3600) }} hours
                                                    {{ floor(($attendance->total_work_seconds % 3600) / 60) }} minutes
                                                @else
                                                    @if($attendance->check_out)
                                                        Calculating...
                                                    @else
                                                        In Progress
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h5>Break History</h5>
                                    @if($attendance->breaks->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Start Time</th>
                                                        <th>End Time</th>
                                                        <th>Duration</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($attendance->breaks as $index => $break)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $break->break_start }}</td>
                                                            <td>{{ $break->break_end ?? 'Ongoing' }}</td>
                                                            <td>
                                                                @if($break->break_seconds)
                                                                    {{ floor($break->break_seconds / 60) }} minutes
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">No breaks taken today.</p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <h5>No attendance record for today.</h5>
                                <p class="mb-0">Click "Check In" to start your attendance for today.</p>
                            </div>
                        @endif

                        <!-- Weekend Info -->
                        @if(now()->isWeekend())
                            <div class="alert alert-warning mt-3">
                                <strong>Weekend Notice:</strong> Today is {{ now()->format('l') }}. Check-ins are disabled on weekends.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auto refresh page every 30 seconds -->
    <script>
        setTimeout(function() {
            location.reload();
        }, 30000); // 30 seconds
    </script>
</body>
</html>