<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Attendance System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        .card-box {
            border-radius: 15px;
            transition: .3s;
        }

        .card-box:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
        }
    </style>

</head>


<body class="bg-light">


    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">

        <div class="container">

            <a class="navbar-brand fw-bold"
                href="/dashboard">

                Attendance System

            </a>


            <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">

                <span class="navbar-toggler-icon"></span>

            </button>



            <div class="collapse navbar-collapse"
                id="navbarNav">


                <ul class="navbar-nav ms-auto">


                    <li class="nav-item">

                        <a class="nav-link active"
                            href="/attendance">

                            Attendance

                        </a>

                    </li>



                    <li class="nav-item">

                        <a class="nav-link"
                            href="/dashboard">

                            Dashboard

                        </a>

                    </li>



                    <li class="nav-item">


                        <form method="POST"
                            action="{{ route('logout') }}">

                            @csrf


                            <button type="submit"
                                class="btn btn-link nav-link">

                                Logout

                            </button>


                        </form>


                    </li>


                </ul>


            </div>


        </div>


    </nav>


    <div class="container mt-4">


        {{-- Success Message --}}

        @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">

            </button>

        </div>

        @endif


        {{-- Error Message --}}

        @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            {{ session('error') }}

            <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">

            </button>

        </div>

        @endif


        <!-- Statistics Cards -->

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-5 g-3 mb-4">

            <!-- Total Attendance -->
            <div class="col">

                <div class="card shadow card-box bg-primary text-white h-100">

                    <div class="card-body text-center">

                        <h6>Total Attendance</h6>

                        <div class="stat-number">
                            {{ $totalAttendance ?? 0 }}
                        </div>

                    </div>

                </div>

            </div>

            <!-- Completed Days -->
            <div class="col">

                <div class="card shadow card-box bg-success text-white h-100">

                    <div class="card-body text-center">

                        <h6>Completed Days</h6>

                        <div class="stat-number">
                            {{ $completedAttendance ?? 0 }}
                        </div>

                    </div>

                </div>

            </div>

            <!-- Total Break Minutes -->
            <div class="col">

                <div class="card shadow card-box bg-warning h-100">

                    <div class="card-body text-center">

                        <h6>Total Break Minutes</h6>

                        <div class="stat-number">
                            {{ $totalBreakMinutes ?? 0 }}
                        </div>

                    </div>

                </div>

            </div>

            <!-- Working Hours -->
            <div class="col">

                <div class="card shadow card-box bg-info text-white h-100">

                    <div class="card-body text-center">

                        <h6>Working Hours</h6>

                        <div class="stat-number">
                            {{ $totalWorkingHours ?? 0 }}
                        </div>

                    </div>

                </div>

            </div>

            <!-- Total Late Days -->
            <div class="col">

                <div class="card shadow card-box bg-danger text-white h-100">

                    <div class="card-body text-center">

                        <h6>Total Late Days</h6>

                        <div class="stat-number">
                            {{ $lateDays ?? 0 }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <form method="GET" class="row g-3 mb-4">

            <div class="col-md-4">

                <input
                    type="month"
                    name="month"
                    class="form-control"
                    value="{{ request('month', now()->format('Y-m')) }}">

            </div>

            <div class="col-md-2">

                <button class="btn btn-primary w-100">

                    View Report

                </button>

            </div>

            <div class="col-md-2">

                <a href="{{ route('attendance') }}"
                    class="btn btn-secondary w-100">

                    Reset

                </a>

            </div>

        </form>

        <!-- ==========================================
       Monthly Attendance Report
      =========================================== -->

        <div class="card shadow mb-4 border-0">

            <div class="card-header bg-dark text-white">

                <h4 class="mb-0">
                    📊 Monthly Attendance Report
                    <small class="float-end">
                        {{ $selectedMonth }}
                    </small>
                </h4>

            </div>

            <div class="card-body">

                <div class="row g-4">

                    <div class="col-md-4 col-lg-2">

                        <div class="card bg-primary text-white text-center shadow">

                            <div class="card-body">

                                <h6>Present Days</h6>

                                <h2>{{ $monthlyPresentDays }}</h2>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4 col-lg-2">

                        <div class="card bg-danger text-white text-center shadow">

                            <div class="card-body">

                                <h6>Late Days</h6>

                                <h2>{{ $monthlyLateDays }}</h2>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4 col-lg-2">

                        <div class="card bg-success text-white text-center shadow">

                            <div class="card-body">

                                <h6>Working Hours</h6>

                                <h2>{{ $monthlyWorkingHours }}</h2>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4 col-lg-2">

                        <div class="card bg-warning text-dark text-center shadow">

                            <div class="card-body">

                                <h6>Break Hours</h6>

                                <h2>{{ $monthlyBreakHours }}</h2>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4 col-lg-2">

                        <div class="card bg-info text-white text-center shadow">

                            <div class="card-body">

                                <h6>Average Hours</h6>

                                <h2>{{ $averageWorkingHours }}</h2>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4 col-lg-2">

                        <div class="card bg-secondary text-white text-center shadow">

                            <div class="card-body">

                                <h6>Attendance %</h6>

                                <h2>{{ $attendancePercentage }}%</h2>

                            </div>

                        </div>

                    </div>

                </div>

                <hr>

                <h5 class="mb-3">

                    Attendance Progress

                </h5>

                <div class="progress" style="height:30px;">

                    <div
                        class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                        role="progressbar"
                        style="width: {{ $attendancePercentage }}%;"
                        aria-valuenow="{{ $attendancePercentage }}"
                        aria-valuemin="0"
                        aria-valuemax="100">

                        {{ $attendancePercentage }}%

                    </div>

                </div>

                <div class="mt-3">

                    @if($attendancePercentage >= 90)

                    <div class="alert alert-success mb-0">

                        🎉 Excellent Attendance!

                    </div>

                    @elseif($attendancePercentage >= 75)

                    <div class="alert alert-warning mb-0">

                        👍 Good Attendance. Keep it up!

                    </div>

                    @else

                    <div class="alert alert-danger mb-0">

                        ⚠ Attendance needs improvement.

                    </div>

                    @endif

                </div>

            </div>

        </div>

        <!-- Today's Attendance Card -->


        <div class="card shadow mb-4">


            <div class="card-header bg-primary text-white">


                <h4 class="mb-0">

                    Today's Attendance
                    ({{ now()->format('d-m-Y h:i:s A') }})

                </h4>


            </div>

            <div class="card-body">


                <!-- Action Buttons -->


                <div class="d-flex flex-wrap gap-3 mb-4">

                    <!-- Check In -->

                    <form method="POST"
                        action="{{ route('check-in') }}">

                        @csrf


                        <button type="submit"
                            class="btn btn-success btn-lg">


                            Check In


                        </button>


                    </form>

                    <!-- Break Start -->

                    <form method="POST"
                        action="{{ route('break-start') }}">

                        @csrf


                        <button type="submit"
                            class="btn btn-warning btn-lg">


                            Break Start


                        </button>


                    </form>


                    <!-- Break End -->

                    <form method="POST"
                        action="{{ route('break-end') }}">

                        @csrf


                        <button type="submit"
                            class="btn btn-info btn-lg text-white">


                            Break End


                        </button>


                    </form>


                    <!-- Check Out -->

                    <form method="POST"
                        action="{{ route('check-out') }}">

                        @csrf


                        <button type="submit"
                            class="btn btn-danger btn-lg">


                            Check Out


                        </button>


                    </form>



                </div>

                <hr>


                {{-- Today's Attendance Details --}}


                @if($attendance)


                <div class="row">


                    <!-- Attendance Summary -->

                    <div class="col-md-6">


                        <div class="card border-primary">


                            <div class="card-header bg-primary text-white">

                                <h5 class="mb-0">
                                    Attendance Summary
                                </h5>

                            </div>


                            <div class="card-body">


                                <table class="table table-bordered">


                                    <tr>

                                        <th width="40%">
                                            Date
                                        </th>

                                        <td>

                                            {{ $attendance->date->format('d-m-Y') }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>
                                            Check In Time
                                        </th>


                                        <td>

                                            {{ $attendance->check_in ?? 'Not Checked In' }}

                                        </td>


                                    </tr>

                                    <tr>

                                        <th>
                                            Check Out Time
                                        </th>


                                        <td>

                                            {{ $attendance->check_out ?? 'Not Checked Out' }}

                                        </td>


                                    </tr>

                                    <tr>

                                        <th>
                                            Total Break Time
                                        </th>


                                        <td>

                                            {{ floor($attendance->total_break_seconds / 60) }}
                                            Minutes


                                        </td>


                                    </tr>

                                    <tr>

                                        <th>
                                            Working Time
                                        </th>


                                        <td>


                                            @if($attendance->total_work_seconds)


                                            {{ floor($attendance->total_work_seconds / 3600) }}
                                            Hours


                                            {{ floor(($attendance->total_work_seconds % 3600) / 60) }}
                                            Minutes


                                            @elseif($attendance->check_in)


                                            <span class="badge bg-warning text-dark">

                                                Working...

                                            </span>


                                            @else


                                            <span class="badge bg-secondary">

                                                Not Started

                                            </span>


                                            @endif



                                        </td>


                                    </tr>



                                    <tr>

                                        <th>Attendance Status</th>

                                        <td>

                                            @if($attendance->status=='Late')

                                            <span class="badge bg-danger">

                                                🔴 Late

                                            </span>

                                            @else

                                            <span class="badge bg-success">

                                                🟢 On Time

                                            </span>

                                            @endif

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>Late Minutes</th>

                                        <td>

                                            {{ $attendance->late_minutes }} Minutes

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>Current Status</th>

                                        <td>

                                            @if($attendance->check_out)

                                            <span class="badge bg-success">

                                                Completed

                                            </span>

                                            @elseif($attendance->check_in)

                                            <span class="badge bg-primary">

                                                Working

                                            </span>

                                            @else

                                            <span class="badge bg-secondary">

                                                Absent

                                            </span>

                                            @endif

                                        </td>

                                    </tr>


                                </table>



                            </div>


                        </div>



                    </div>


                    <!-- Break History -->

                    <div class="col-md-6">


                        <div class="card border-warning">


                            <div class="card-header bg-warning">


                                <h5 class="mb-0">

                                    Break History

                                </h5>


                            </div>



                            <div class="card-body">



                                @if($attendance->breaks->count() > 0)



                                <div class="table-responsive">


                                    <table class="table table-striped">


                                        <thead>


                                            <tr>

                                                <th>
                                                    #
                                                </th>


                                                <th>
                                                    Start
                                                </th>


                                                <th>
                                                    End
                                                </th>


                                                <th>
                                                    Duration
                                                </th>


                                            </tr>


                                        </thead>



                                        <tbody>



                                            @foreach($attendance->breaks as $index=>$break)



                                            <tr>


                                                <td>

                                                    {{ $index + 1 }}

                                                </td>



                                                <td>

                                                    {{ $break->break_start }}

                                                </td>




                                                <td>

                                                    {{ $break->break_end ?? 'Running' }}

                                                </td>




                                                <td>


                                                    @if($break->break_seconds)


                                                    {{ floor($break->break_seconds / 60) }}
                                                    min


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


                                <div class="alert alert-info">


                                    No breaks taken today.


                                </div>



                                @endif




                            </div>


                        </div>


                    </div>



                </div>




                @else



                <div class="alert alert-info">


                    <h5>
                        No Attendance Record Found
                    </h5>


                    <p class="mb-0">

                        Click Check In button to start attendance.

                    </p>


                </div>

                <br><br>

                @endif

                <!-- Search & Filter Section -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-dark text-white">

                        <h5 class="mb-0">
                            Attendance Search & Filter
                        </h5>

                    </div>


                    <div class="card-body">


                        <form method="GET"
                            action="{{ route('attendance') }}">


                            <div class="row g-3">


                                <!-- Search -->

                                <div class="col-md-5">


                                    <label class="form-label">
                                        Search Date
                                    </label>


                                    <input type="date"
                                        name="search"
                                        class="form-control"
                                        value="{{ request('search') }}">


                                </div>




                                <!-- Status Filter -->

                                <div class="col-md-4">


                                    <label class="form-label">
                                        Status
                                    </label>


                                    <select name="status"
                                        class="form-select">


                                        <option value="">
                                            All Status
                                        </option>


                                        <option value="checked_in"
                                            {{ request('status') == 'checked_in' ? 'selected' : '' }}>

                                            Checked In

                                        </option>


                                        <option value="checked_out"
                                            {{ request('status') == 'checked_out' ? 'selected' : '' }}>

                                            Checked Out

                                        </option>



                                        <option value="working"
                                            {{ request('status') == 'working' ? 'selected' : '' }}>

                                            Working

                                        </option>



                                        <option value="completed"
                                            {{ request('status') == 'completed' ? 'selected' : '' }}>

                                            Completed

                                        </option>


                                    </select>


                                </div>


                                <!-- Buttons -->

                                <div class="col-md-3 d-flex align-items-end gap-2">


                                    <button type="submit"
                                        class="btn btn-primary">

                                        <i class="bi bi-search"></i>
                                        Search

                                    </button>



                                    <a href="{{ route('attendance') }}"
                                        class="btn btn-secondary">

                                        Reset

                                    </a>


                                </div>



                            </div>



                        </form>



                        <hr>


                        <!-- Export Button -->


                        <a href="{{ route('attendance.export', [
                    'search'=>request('search'),
                    'status'=>request('status')
                ]) }}"
                            class="btn btn-success">


                            Export CSV


                        </a>



                    </div>


                </div>

                <hr>


                <!-- Attendance History Section -->


                <div class="card shadow mt-4">


                    <div class="card-header bg-dark text-white">


                        <h4 class="mb-0">

                            Attendance History

                        </h4>


                    </div>


                    <div class="card-body">


                        @if($attendanceHistory->count() > 0)



                        <div class="table-responsive">


                            <table class="table table-bordered table-hover">


                                <thead class="table-dark">


                                    <tr>

                                        <th>
                                            #
                                        </th>


                                        <th>
                                            Date
                                        </th>


                                        <th>
                                            Check In
                                        </th>


                                        <th>
                                            Check Out
                                        </th>


                                        <th>
                                            Break Time
                                        </th>


                                        <th>
                                            Working Hours
                                        </th>

                                        <th>Attendance</th>

                                        <th>Late By</th>

                                        <th>
                                            Status
                                        </th>


                                    </tr>


                                </thead>



                                <tbody>

                                    @foreach($attendanceHistory as $index=>$history)



                                    <tr>

                                        <td>

                                            {{
                                                $attendanceHistory->firstItem() + $index
                                            }}

                                        </td>


                                        <td>


                                            {{
                                                $history->date->format('d-m-Y')
                                            }}


                                        </td>


                                        <td>


                                            {{
                                                $history->check_in ?? '-'
                                            }}


                                        </td>


                                        <td>


                                            {{
                                                $history->check_out ?? '-'
                                            }}


                                        </td>


                                        <td>


                                            {{
                                                floor($history->total_break_seconds / 60)
                                            }}

                                            Minutes


                                        </td>


                                        <td>



                                            @if($history->total_work_seconds)



                                            {{
                                                    floor($history->total_work_seconds / 3600)
                                                }}
                                            Hours



                                            {{
                                                    floor(
                                                        ($history->total_work_seconds % 3600) / 60
                                                    )
                                                }}
                                            Minutes



                                            @else


                                            -


                                            @endif


                                        </td>

                                        <!-- NEW Attendance Status -->
                                        <td>

                                            @if($history->status == 'Late')

                                            <span class="badge bg-danger">
                                                Late
                                            </span>

                                            @else

                                            <span class="badge bg-success">
                                                On Time
                                            </span>

                                            @endif

                                        </td>

                                        <!-- NEW Late Minutes -->
                                        <td>

                                            {{ $history->late_minutes }} Minutes

                                        </td>


                                        <td>

                                            @if($history->check_out)



                                            <span class="badge bg-success">

                                                Completed

                                            </span>



                                            @elseif($history->check_in)



                                            <span class="badge bg-primary">

                                                Working

                                            </span>


                                            @else


                                            <span class="badge bg-secondary">

                                                Absent

                                            </span>

                                            @endif


                                        </td>


                                    </tr>


                                    @endforeach


                                </tbody>


                            </table>


                        </div>



                        @else



                        <div class="alert alert-warning">


                            No attendance history found.


                        </div>



                        @endif



                    </div>


                </div>

                <!-- Pagination -->


                @if($attendanceHistory->hasPages())


                <div class="mt-4 d-flex justify-content-center">

                    {{ $attendanceHistory->links() }}

                </div>


                @endif


                <!-- Weekend Information -->


                @if(now()->isWeekend())


                <div class="alert alert-warning mt-4">


                    <strong>
                        Weekend Notice:
                    </strong>


                    Today is {{ now()->format('l') }}.


                    Check-in is disabled on weekends.


                </div>


                @endif


            </div>


        </div>


        <!-- Bootstrap JS -->

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
        </script>

        <!-- Auto Refresh -->


        <script>
            setTimeout(function() {


                location.reload();


            }, 30000);
        </script>

</body>


</html>