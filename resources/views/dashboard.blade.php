<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">Attendance System</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/attendance">Go to Attendance</a>
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
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Welcome, {{ Auth::user()->name }}!</h4>
                    </div>
                    <div class="card-body">
                        <h5>Attendance System Features:</h5>
                        <ul>
                            <li>✅ Indian Timezone (Asia/Kolkata)</li>
                            <li>✅ One Check-In per day</li>
                            <li>✅ Multiple breaks allowed</li>
                            <li>✅ Break validation (no overlapping)</li>
                            <li>✅ Weekend restriction (Sat-Sun)</li>
                            <li>✅ 8 hours minimum work required</li>
                            <li>✅ Seconds-based calculations</li>
                            <li>✅ Clean and simple interface</li>
                        </ul>
                        
                        <div class="mt-4">
                            <a href="/attendance" class="btn btn-success btn-lg">Go to Attendance Page</a>
                        </div>

                        <hr>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Current Time</h6>
                                        <p class="display-6">{{ now()->format('h:i:s A') }}</p>
                                        <small>{{ now()->format('l, F j, Y') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Weekend Status</h6>
                                        @if(now()->isWeekend())
                                            <p class="text-danger">Today is weekend - No check-in allowed</p>
                                        @else
                                            <p class="text-success">Working day - You can check in</p>
                                        @endif
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