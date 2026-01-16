# PHP_Laravel12_CheckIn_CheckOut_System

A complete employee attendance management system built with **Laravel 12** that tracks check-ins, check-outs, breaks, and accurately calculates working hours using the **Indian timezone (Asia/Kolkata)**.

---

## Features

### Core Features

* User Authentication (Login & Registration)
* Indian Timezone Support (Asia/Kolkata)
* Daily Check-In System (one check-in per day)
* Multiple Breaks Management (unlimited breaks per day)
* Break Start and Break End with validation (no overlapping breaks)
* Weekend Restriction (Saturday and Sunday check-in blocked)
* Accurate Time Calculation (seconds-based, no rounding errors)
* Minimum 8 Working Hours Requirement
* Clean database design with proper relationships

### Technical Features

* Laravel 12 with Blade templates
* Bootstrap 5 for responsive UI
* MySQL database
* Carbon for date and time manipulation
* CSRF protection
* Session-based notifications

---

## Prerequisites

* PHP 8.2 or higher
* Composer
* MySQL 5.7 or higher
* Node.js and NPM (for asset compilation)
* Web server (Apache / Nginx) or PHP built-in server

---

## Installation

### Step 1: Clone the Project

```bash
git clone https://github.com/yourusername/attendance-system.git
cd attendance-system
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install JavaScript Dependencies

```bash
npm install
npm run build
```

### Step 4: Configure Environment

```bash
cp .env.example .env
```

Update your database and timezone settings in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_system
DB_USERNAME=root
DB_PASSWORD=your_password

APP_TIMEZONE=Asia/Kolkata
APP_URL=http://localhost:8000
```

### Step 5: Generate Application Key

```bash
php artisan key:generate
```

### Step 6: Run Database Migrations

```bash
php artisan migrate
```

### Step 7: Start Development Server

```bash
php artisan serve
```

Application URL: `http://localhost:8000`

---

## Database Structure

### Tables

#### 1. users (Default Laravel Auth Table)

```
id
name
email
email_verified_at
password
remember_token
created_at
updated_at
```

#### 2. attendances

```
id
user_id
date
check_in
check_out
total_break_seconds
total_work_seconds
created_at
updated_at
```

#### 3. breaks

```
id
attendance_id
break_start
break_end
break_seconds
created_at
updated_at
```

---

## Project Structure

```
attendance-system/
├── app/
│   ├── Http/Controllers/AttendanceController.php
│   ├── Models/Attendance.php
│   ├── Models/BreakTime.php
├── database/migrations/
├── resources/views/
│   ├── attendance.blade.php
│   ├── dashboard.blade.php
│   └── auth/
├── routes/web.php
└── public/
```

---

## Usage Guide

### 1. Registration and Login

* Visit `/register` to create a new account
* Visit `/login` to access an existing account
* After login, you will be redirected to the dashboard

### 2. Attendance Page

* Access attendance page at `/attendance`
* Shows current date and time in Indian timezone
* Displays all attendance actions

---

## Attendance Workflow

### Step 1: Check In

* Click **Check In** to start your day
* Available only on weekdays (Monday to Friday)
* Can only check in once per day
* Time recorded in HH:MM:SS format

### Step 2: Take Breaks

* Click **Break Start** to begin a break
* Click **Break End** to finish the break
* Multiple breaks allowed per day
* Break duration calculated automatically
* Cannot start a new break if one is already active

### Step 3: Check Out

* Click **Check Out** to end your workday
* System validates minimum 8 working hours
* Working time = (Check Out - Check In) - Total Breaks
* If less than 8 hours, an error message is shown

---

## Viewing Attendance Data

* Current day check-in and check-out times
* Total break time in minutes
* Total working time in hours and minutes
* Break history with start and end times

---

## Business Rules

### Time Calculations

* All calculations are done in seconds
* Working time = (Check Out - Check In) - Total Break Time
* Minimum working time: 8 hours (28,800 seconds)

### Restrictions

* No check-in on weekends (Saturday and Sunday)
* One check-in per user per day
* Must check in before taking breaks
* Must check in before checking out
* Cannot start a break if one is already active
* Cannot check out without completing minimum hours

---

## Routes

| Method | URL          | Action     | Description      |
| ------ | ------------ | ---------- | ---------------- |
| GET    | /attendance  | index      | Attendance page  |
| POST   | /check-in    | checkIn    | Record check-in  |
| POST   | /break-start | breakStart | Start break      |
| POST   | /break-end   | breakEnd   | End break        |
| POST   | /check-out   | checkOut   | Record check-out |

---

## Timezone Configuration

* Timezone: Asia/Kolkata
* UTC Offset: +5:30
* All timestamps stored and calculated in IST

---

## Screenshot
<img width="1698" height="792" alt="image" src="https://github.com/user-attachments/assets/530c3b65-fa0b-4830-96cb-aa5d1463e7af" />
<img width="1631" height="888" alt="image" src="https://github.com/user-attachments/assets/772179c3-eb6f-44f2-8d5e-edba3dd9b711" />



## Validation Rules

### Check In

* User must be authenticated
* Current day must be a weekday
* No existing check-in for the day

### Break Start

* User must have checked in
* No active break already running

### Break End

* User must have an active break

### Check Out

* User must have checked in
* Minimum 8 working hours completed
* Not already checked out

---

## Customization

### Change Minimum Working Hours

Edit `AttendanceController.php`:

```php
if ($workSeconds < (8 * 3600)) {
```

### Modify Weekend Days

```php
if (Carbon::now()->isWeekend()) {
```

### Change Timezone

Edit `config/app.php`:

```php
'timezone' => 'Asia/Kolkata',
```

---

## Testing Scenarios

* Normal workday with multiple breaks
* Early checkout (should fail)
* Weekend check-in (should fail)
* Multiple breaks in a single day

---

## Troubleshooting

* Database access denied: verify `.env` credentials
* Carbon not found: run `composer install`
* Token mismatch: ensure `APP_KEY` exists
* Assets not loading: run `npm run build`
* Timezone issues: verify `APP_TIMEZONE`
