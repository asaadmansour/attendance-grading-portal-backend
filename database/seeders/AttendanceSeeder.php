<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceLedger;
use App\Models\Enrollment;
use App\Models\ExcuseRequest;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();

        foreach ($students as $student) {
            AttendanceLedger::firstOrCreate(
                ['student_id' => $student->id],
                ['balance' => 250]
            );
        }

        $this->command->info("Seeded {$students->count()} attendance ledgers.");

        $sessions = Session::where('is_delivered', true)->get();

        if ($sessions->isEmpty()) {
            $this->command->warn('No delivered sessions found — skipping attendance seeding.');
            return;
        }

        foreach ($sessions as $session) {
            $cohortId = $session->engagement->cohort_id;
            $enrollments = Enrollment::where('cohort_id', $cohortId)->get();

            foreach ($enrollments as $enrollment) {
                $studentId = $enrollment->student_id;

                //80% present, 15% unexcused absent, 5% excused absent
                $rand = rand(1, 100);

                DB::transaction(function () use ($session, $studentId, $rand) {
                    if ($rand <= 80) {
                        Attendance::firstOrCreate(
                            ['session_id' => $session->id, 'student_id' => $studentId],
                            [
                                'arrived_at' => $session->session_date->setTime(9, rand(0, 10)),
                                'left_at'    => $session->session_date->setTime(14, rand(50, 59)),
                                'status'     => 'present',
                            ]
                        );
                    } elseif ($rand <= 95) {
                        $att = Attendance::firstOrCreate(
                            ['session_id' => $session->id, 'student_id' => $studentId],
                            ['status' => 'absent']
                        );

                        $ledger = AttendanceLedger::where('student_id', $studentId)->first();
                        if ($ledger) {
                            $ledger->deduct(25);
                        }
                    } else {
                        $att = Attendance::firstOrCreate(
                            ['session_id' => $session->id, 'student_id' => $studentId],
                            ['status' => 'excused']
                        );

                        $ledger = AttendanceLedger::where('student_id', $studentId)->first();
                        if ($ledger) {
                            $ledger->deduct(5);
                        }

                        ExcuseRequest::firstOrCreate(
                            ['attendance_id' => $att->id],
                            [
                                'student_id'  => $studentId,
                                'reason'      => 'Medical appointment (seeded)',
                                'status'      => ExcuseRequest::STATUS_APPROVED,
                                'reviewed_by' => User::where('role', 'track_admin')->first()?->id,
                            ]
                        );
                    }
                });
            }
        }

        $this->command->info('Attendance records seeded.');
    }
}