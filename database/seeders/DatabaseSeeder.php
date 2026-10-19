<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'HR Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'employee_id' => 'EMP-0001',
                'department' => 'Human Resources',
                'designation' => 'HR Operations Lead',
                'casual_leave_quota' => 12.0,
                'sick_leave_quota' => 10.0,
                'emergency_leave_quota' => 5.0,
                'wfh_quota' => 15.0,
                'email_verified_at' => now(),
            ]
        );

        // 2. Regular Employees
        $alex = User::firstOrCreate(
            ['email' => 'alex@example.com'],
            [
                'name' => 'Alex Rivera',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'employee_id' => 'EMP-0101',
                'department' => 'Software Engineering',
                'designation' => 'Senior Backend Developer',
                'casual_leave_quota' => 12.0,
                'sick_leave_quota' => 10.0,
                'emergency_leave_quota' => 5.0,
                'wfh_quota' => 15.0,
                'email_verified_at' => now(),
            ]
        );

        $sarah = User::firstOrCreate(
            ['email' => 'sarah@example.com'],
            [
                'name' => 'Sarah Connor',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'employee_id' => 'EMP-0102',
                'department' => 'Product & UI/UX',
                'designation' => 'Product Designer',
                'casual_leave_quota' => 12.0,
                'sick_leave_quota' => 10.0,
                'emergency_leave_quota' => 5.0,
                'wfh_quota' => 15.0,
                'email_verified_at' => now(),
            ]
        );

        $john = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'employee_id' => 'EMP-0103',
                'department' => 'Finance & Accounting',
                'designation' => 'Financial Analyst',
                'casual_leave_quota' => 12.0,
                'sick_leave_quota' => 10.0,
                'emergency_leave_quota' => 5.0,
                'wfh_quota' => 15.0,
                'email_verified_at' => now(),
            ]
        );

        // 3. Seed Realistic Attendance History for Alex (October 2026)
        $monthDays = [
            // Date, In, Out, BreakSecs, WorkSecs, Status, LateMins, OTSecs, EarlyMins, HalfDay, EarlyReason, Type
            ['2026-10-01', '09:12:00', '17:45:00', 2700, 27980, 'On Time', 0, 0, 0, false, null, 'present'], // Normal full day (7h 46m)
            ['2026-10-02', '09:05:00', '19:15:00', 3600, 33000, 'On Time', 0, 4200, 0, false, null, 'present'], // Overtime (+1h 10m)
            // Oct 3 & 4 Weekend
            ['2026-10-05', '09:48:00', '18:10:00', 2400, 27920, 'Late', 18, 0, 0, false, null, 'present'], // Late arrival
            ['2026-10-06', '09:10:00', '19:50:00', 3000, 35400, 'On Time', 0, 6600, 0, false, null, 'present'], // Overtime (+1h 50m)
            ['2026-10-07', '09:15:00', '16:30:00', 2100, 23900, 'On Time', 0, 0, 81, false, 'Doctor consultation at 5 PM', 'present'], // Early departure
            ['2026-10-08', '09:20:00', '13:00:00', 1200, 12000, 'On Time', 0, 0, 280, true, 'Urgent family emergency in the afternoon', 'half_day'], // Half day
            ['2026-10-09', '09:00:00', '18:30:00', 3600, 30600, 'On Time', 0, 1800, 0, false, null, 'present'], // Overtime (+30m)
            // Oct 10 & 11 Weekend
            ['2026-10-12', '09:55:00', '18:25:00', 2700, 27900, 'Late', 25, 0, 0, false, null, 'present'], // Late arrival
            ['2026-10-13', '09:10:00', '18:00:00', 3600, 28200, 'On Time', 0, 0, 10, false, null, 'present'], // Regular
            ['2026-10-14', '09:15:00', '19:30:00', 3000, 33900, 'On Time', 0, 5100, 0, false, null, 'present'], // Overtime (+1h 25m)
            ['2026-10-15', '09:00:00', '17:30:00', 2700, 27900, 'On Time', 0, 0, 0, false, null, 'present'], // Normal
            ['2026-10-16', '09:35:00', '18:40:00', 3600, 29100, 'Late', 5, 300, 0, false, null, 'present'], // Late but worked OT (+5m)
        ];

        foreach ($monthDays as $data) {
            $att = Attendance::updateOrCreate(
                [
                    'user_id' => $alex->id,
                    'date' => $data[0],
                ],
                [
                    'check_in' => $data[1],
                    'check_out' => $data[2],
                    'total_break_seconds' => $data[3],
                    'total_work_seconds' => $data[4],
                    'status' => $data[5],
                    'late_minutes' => $data[6],
                    'overtime_seconds' => $data[7],
                    'early_departure_minutes' => $data[8],
                    'is_half_day' => $data[9],
                    'early_departure_reason' => $data[10],
                    'attendance_type' => $data[11],
                ]
            );

            // Add break sample
            if ($data[3] > 0) {
                BreakTime::firstOrCreate(
                    [
                        'attendance_id' => $att->id,
                        'break_start' => '13:00:00',
                    ],
                    [
                        'break_end' => Carbon::parse('13:00:00')->addSeconds($data[3])->format('H:i:s'),
                        'break_seconds' => $data[3],
                    ]
                );
            }
        }

        // Today's attendance for Alex (in-progress shift for testing buttons)
        $todayStr = now()->format('Y-m-d');
        if (!now()->isWeekend()) {
            $todayAtt = Attendance::updateOrCreate(
                [
                    'user_id' => $alex->id,
                    'date' => $todayStr,
                ],
                [
                    'check_in' => '09:15:00',
                    'check_out' => null,
                    'total_break_seconds' => 1800,
                    'total_work_seconds' => 0,
                    'status' => 'On Time',
                    'late_minutes' => 0,
                    'attendance_type' => 'present',
                ]
            );

            BreakTime::firstOrCreate(
                [
                    'attendance_id' => $todayAtt->id,
                    'break_start' => '12:30:00',
                ],
                [
                    'break_end' => '13:00:00',
                    'break_seconds' => 1800,
                ]
            );
        }

        // 4. Seed Leave Applications
        // Approved Sick Leave for Alex (Oct 20)
        $sickLeave = Leave::firstOrCreate(
            [
                'user_id' => $alex->id,
                'start_date' => '2026-10-20',
                'end_date' => '2026-10-20',
            ],
            [
                'leave_type' => 'sick',
                'days_count' => 1.0,
                'is_half_day' => false,
                'reason' => 'Viral fever and prescribed medical rest by doctor.',
                'status' => 'approved',
                'admin_remarks' => 'Approved by HR Administrator. Get well soon!',
                'approved_by' => $admin->id,
                'actioned_at' => Carbon::parse('2026-10-18 10:00:00'),
            ]
        );

        // Sync into attendance
        Attendance::updateOrCreate(
            ['user_id' => $alex->id, 'date' => '2026-10-20'],
            ['attendance_type' => 'on_leave', 'leave_id' => $sickLeave->id, 'status' => 'On Time']
        );

        // Approved WFH for Alex (Oct 21 - Oct 22)
        $wfhLeave = Leave::firstOrCreate(
            [
                'user_id' => $alex->id,
                'start_date' => '2026-10-21',
                'end_date' => '2026-10-22',
            ],
            [
                'leave_type' => 'wfh',
                'days_count' => 2.0,
                'is_half_day' => false,
                'reason' => 'Home appliance repair and technician visit scheduled.',
                'status' => 'approved',
                'admin_remarks' => 'WFH approved. Ensure sprint availability on Slack.',
                'approved_by' => $admin->id,
                'actioned_at' => Carbon::parse('2026-10-18 11:30:00'),
            ]
        );

        Attendance::updateOrCreate(
            ['user_id' => $alex->id, 'date' => '2026-10-21'],
            ['attendance_type' => 'wfh', 'leave_id' => $wfhLeave->id, 'status' => 'On Time']
        );
        Attendance::updateOrCreate(
            ['user_id' => $alex->id, 'date' => '2026-10-22'],
            ['attendance_type' => 'wfh', 'leave_id' => $wfhLeave->id, 'status' => 'On Time']
        );

        // Pending Casual Leave for Alex (Oct 26 - Oct 27)
        Leave::firstOrCreate(
            [
                'user_id' => $alex->id,
                'start_date' => '2026-10-26',
                'end_date' => '2026-10-27',
            ],
            [
                'leave_type' => 'casual',
                'days_count' => 2.0,
                'is_half_day' => false,
                'reason' => 'Attending brother\'s graduation ceremony out of state.',
                'status' => 'pending',
            ]
        );

        // Pending Leave for Sarah Connor (Oct 28)
        Leave::firstOrCreate(
            [
                'user_id' => $sarah->id,
                'start_date' => '2026-10-28',
                'end_date' => '2026-10-28',
            ],
            [
                'leave_type' => 'casual',
                'days_count' => 1.0,
                'is_half_day' => false,
                'reason' => 'Personal errands and bank appointments.',
                'status' => 'pending',
            ]
        );

        // Rejected Leave for John Doe (Past request)
        Leave::firstOrCreate(
            [
                'user_id' => $john->id,
                'start_date' => '2026-10-14',
                'end_date' => '2026-10-16',
            ],
            [
                'leave_type' => 'casual',
                'days_count' => 3.0,
                'is_half_day' => false,
                'reason' => 'Short road trip vacation.',
                'status' => 'rejected',
                'admin_remarks' => 'Rejected due to critical quarterly audit filing deadlines.',
                'approved_by' => $admin->id,
                'actioned_at' => Carbon::parse('2026-10-13 14:00:00'),
            ]
        );
    }
}
