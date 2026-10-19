<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add employee details and role to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('employee')->after('email'); // admin or employee
            $table->string('employee_id')->nullable()->after('role');
            $table->string('department')->nullable()->after('employee_id');
            $table->string('designation')->nullable()->after('department');
            $table->decimal('casual_leave_quota', 4, 1)->default(12.0)->after('designation');
            $table->decimal('sick_leave_quota', 4, 1)->default(10.0)->after('casual_leave_quota');
            $table->decimal('emergency_leave_quota', 4, 1)->default(5.0)->after('sick_leave_quota');
            $table->decimal('wfh_quota', 4, 1)->default(15.0)->after('emergency_leave_quota');
        });

        // 2. Create leaves table
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('leave_type', ['casual', 'sick', 'emergency', 'wfh']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_count', 4, 1)->default(1.0);
            $table->boolean('is_half_day')->default(false);
            $table->enum('half_day_type', ['first_half', 'second_half'])->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_remarks')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();
        });

        // 3. Add overtime, early departure, and leave reference to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->integer('overtime_seconds')->default(0)->after('total_work_seconds');
            $table->integer('early_departure_minutes')->default(0)->after('overtime_seconds');
            $table->boolean('is_half_day')->default(false)->after('early_departure_minutes');
            $table->text('early_departure_reason')->nullable()->after('is_half_day');
            $table->enum('attendance_type', ['present', 'on_leave', 'wfh', 'half_day'])->default('present')->after('early_departure_reason');
            $table->foreignId('leave_id')->nullable()->after('attendance_type')->constrained('leaves')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['leave_id']);
            $table->dropColumn([
                'overtime_seconds',
                'early_departure_minutes',
                'is_half_day',
                'early_departure_reason',
                'attendance_type',
                'leave_id'
            ]);
        });

        Schema::dropIfExists('leaves');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'employee_id',
                'department',
                'designation',
                'casual_leave_quota',
                'sick_leave_quota',
                'emergency_leave_quota',
                'wfh_quota'
            ]);
        });
    }
};
