<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">

        <div class="container">

            <a class="navbar-brand" href="{{ route('dashboard') }}">
                Attendance System
            </a>

            <div class="collapse navbar-collapse">

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">

                        <a class="nav-link" href="{{ route('attendance') }}">
                            Attendance Dashboard
                        </a>

                    </li>

                    <li class="nav-item">

                        <form method="POST" action="{{ route('logout') }}">

                            @csrf

                            <button type="submit" class="nav-link btn btn-link text-white">
                                Logout
                            </button>

                        </form>

                    </li>

                </ul>

            </div>

        </div>

    </nav>

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-lg-10">

                <div class="card shadow border-0">

                    <div class="card-header bg-primary text-white">

                        <h3 class="mb-1">
                            👋 Welcome, {{ Auth::user()->name }}
                        </h3>

                        <p class="mb-0">
                            Manage your attendance, breaks, working hours and monthly reports.
                        </p>

                    </div>

                    <div class="card-body">

                        <h5 class="mb-3">
                            Attendance System Features
                        </h5>

                        <ul class="list-group list-group-flush mb-4">

                            <li class="list-group-item">
                                ✅ Indian Timezone (Asia/Kolkata)
                            </li>

                            <li class="list-group-item">
                                ✅ One Check-In Per Day
                            </li>

                            <li class="list-group-item">
                                ✅ Multiple Break Management
                            </li>

                            <li class="list-group-item">
                                ✅ Break Validation (No Overlapping Breaks)
                            </li>

                            <li class="list-group-item">
                                ✅ Weekend Check-In Restriction
                            </li>

                            <li class="list-group-item">
                                ✅ Minimum 8 Working Hours Validation
                            </li>

                            <li class="list-group-item">
                                ✅ Late Check-In Detection
                            </li>

                            <li class="list-group-item">
                                ✅ Monthly Attendance Reports
                            </li>

                            <li class="list-group-item">
                                ✅ Dashboard Statistics
                            </li>

                            <li class="list-group-item">
                                ✅ Attendance History Search & Filters
                            </li>

                            <li class="list-group-item">
                                ✅ CSV Export
                            </li>

                        </ul>

                        <div class="text-center mb-4">

                            <a href="{{ route('attendance') }}"
                                class="btn btn-success btn-lg px-5">

                                Open Attendance Dashboard

                            </a>

                        </div>

                        <hr>

                        <div class="row g-4">

                            <div class="col-md-6">

                                <div class="card border-primary shadow-sm h-100">

                                    <div class="card-body">

                                        <h5>
                                            🕒 Current Time
                                        </h5>

                                        <h2 class="text-primary">
                                            {{ now()->format('h:i:s A') }}
                                        </h2>

                                        <p class="text-muted mb-0">
                                            {{ now()->format('l, d F Y') }}
                                        </p>

                                    </div>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="card border-success shadow-sm h-100">

                                    <div class="card-body">

                                        <h5>
                                            📅 Weekend Status
                                        </h5>

                                        @if(now()->isWeekend())

                                        <div class="alert alert-danger mb-0">

                                            Weekend Detected

                                            <br>

                                            Check-In is not allowed today.

                                        </div>

                                        @else

                                        <div class="alert alert-success mb-0">

                                            Working Day

                                            <br>

                                            You can check in today.

                                        </div>

                                        @endif

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="row g-4 mt-2">

                            <div class="col-md-6">

                                <div class="card border-warning shadow-sm h-100">

                                    <div class="card-body">

                                        <h5>
                                            ⏰ Office Timing
                                        </h5>

                                        <p class="mb-2">

                                            Office Start Time:

                                            <strong>
                                                09:30 AM
                                            </strong>

                                        </p>

                                        <span class="badge bg-danger">
                                            Late Check-In Rule
                                        </span>

                                        <p class="mt-2 mb-0 text-muted">

                                            Employees checking in after
                                            <strong>09:30 AM</strong>
                                            will automatically be marked as
                                            <strong>Late</strong>
                                            and late minutes will be recorded.

                                        </p>

                                    </div>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="card border-info shadow-sm h-100">

                                    <div class="card-body">

                                        <h5>
                                            📊 Reports & Analytics
                                        </h5>

                                        <ul class="mb-0">

                                            <li>Monthly Attendance Report</li>

                                            <li>Attendance Percentage</li>

                                            <li>Total Working Hours</li>

                                            <li>Total Late Days</li>

                                            <li>Attendance Progress Bar</li>

                                            <li>CSV Export</li>

                                        </ul>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>