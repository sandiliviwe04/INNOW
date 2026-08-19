<?php

namespace Innow\Controllers;

use Innow\Models\User;
use Innow\Models\WorkSchedule;
use Innow\Models\Compensation;
use Innow\Models\PayrollRecord;
use Innow\Services\PayrollService;
use Innow\Utils\ResponseHelper;
use Innow\Utils\Validator;
use Innow\Middleware\AuthMiddleware;
use Innow\Middleware\CsrfMiddleware;

class PayrollController {

    /** Returns the logged-in admin user, or writes a 401/403 response and returns null. */
    private function requireAdmin(): ?array {
        $user = AuthMiddleware::check();
        if (!$user) {
            ResponseHelper::error('Authentication required.', 401);
            return null;
        }
        $isAdmin = stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator';
        if (!$isAdmin) {
            ResponseHelper::error('Forbidden — Admin access required.', 403);
            return null;
        }
        return $user;
    }

    private function validateCsrf(array $user): bool {
        $sessionToken = $user['token'] ?? '';
        $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        if (!$sessionToken || !$clientToken || !CsrfMiddleware::validateToken($sessionToken, $clientToken)) {
            ResponseHelper::error('Invalid or missing CSRF token. Please refresh the page and try again.', 403);
            return false;
        }
        return true;
    }

    /** GET /api/payroll/schedules?user_id=STF-1001 */
    public function getSchedules(): void {
        $user = $this->requireAdmin();
        if (!$user) return;

        $userId = trim($_GET['user_id'] ?? '');
        if (!$userId) {
            ResponseHelper::error('user_id is required.', 400);
            return;
        }
        if (!User::find($userId)) {
            ResponseHelper::error('Staff member not found.', 404);
            return;
        }

        ResponseHelper::success(['schedules' => WorkSchedule::allForUser($userId)]);
    }

    /** POST /api/payroll/schedules — creates a NEW schedule version (insert-only). */
    public function saveSchedule(): void {
        $user = $this->requireAdmin();
        if (!$user) return;
        if (!$this->validateCsrf($user)) return;

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $errors = Validator::validate($input, [
            'user_id' => 'required',
            'hours_per_day' => 'required',
            'hours_per_week' => 'required',
            'working_days' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'effective_date' => 'required',
        ]);
        if (!empty($errors)) {
            ResponseHelper::error('Validation failed', 422, $errors);
            return;
        }

        if (!User::find($input['user_id'])) {
            ResponseHelper::error('Staff member not found.', 404);
            return;
        }

        $hoursPerDay = (float) $input['hours_per_day'];
        $hoursPerWeek = (float) $input['hours_per_week'];
        if ($hoursPerDay <= 0 || $hoursPerDay > 24) {
            ResponseHelper::error('Hours per day must be between 0 and 24.', 400);
            return;
        }
        if ($hoursPerWeek <= 0 || $hoursPerWeek > 168) {
            ResponseHelper::error('Hours per week must be between 0 and 168.', 400);
            return;
        }

        $workingDays = array_values(array_unique(array_filter(
            array_map('intval', explode(',', $input['working_days'])),
            fn($d) => $d >= 1 && $d <= 7
        )));
        if (empty($workingDays)) {
            ResponseHelper::error('At least one working day is required.', 400);
            return;
        }
        sort($workingDays);

        $schedule = WorkSchedule::create([
            'user_id' => $input['user_id'],
            'hours_per_day' => $hoursPerDay,
            'hours_per_week' => $hoursPerWeek,
            'working_days' => implode(',', $workingDays),
            'start_time' => $input['start_time'],
            'end_time' => $input['end_time'],
            'break_duration_minutes' => (int) ($input['break_duration_minutes'] ?? 0),
            'break_paid' => !empty($input['break_paid']),
            'effective_date' => $input['effective_date'],
            'created_by' => $user['id'] ?? null,
        ]);

        ResponseHelper::success(['schedule' => $schedule], 'Working hours schedule saved.');
    }

    /** GET /api/payroll/compensation?user_id=STF-1001 */
    public function getCompensation(): void {
        $user = $this->requireAdmin();
        if (!$user) return;

        $userId = trim($_GET['user_id'] ?? '');
        if (!$userId) {
            ResponseHelper::error('user_id is required.', 400);
            return;
        }
        if (!User::find($userId)) {
            ResponseHelper::error('Staff member not found.', 404);
            return;
        }

        ResponseHelper::success(['compensation' => Compensation::allForUser($userId)]);
    }

    /** POST /api/payroll/compensation — creates a NEW compensation version (insert-only). */
    public function saveCompensation(): void {
        $user = $this->requireAdmin();
        if (!$user) return;
        if (!$this->validateCsrf($user)) return;

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $errors = Validator::validate($input, [
            'user_id' => 'required',
            'compensation_type' => 'required',
            'effective_date' => 'required',
        ]);
        if (!empty($errors)) {
            ResponseHelper::error('Validation failed', 422, $errors);
            return;
        }

        if (!User::find($input['user_id'])) {
            ResponseHelper::error('Staff member not found.', 404);
            return;
        }

        $type = strtoupper(trim($input['compensation_type']));
        if (!in_array($type, ['HOURLY', 'MONTHLY'], true)) {
            ResponseHelper::error('compensation_type must be HOURLY or MONTHLY.', 400);
            return;
        }

        $hourlyRate = null;
        $monthlySalary = null;

        if ($type === 'HOURLY') {
            $hourlyRate = (float) ($input['hourly_rate'] ?? 0);
            if ($hourlyRate <= 0) {
                ResponseHelper::error('Hourly rate must be greater than 0.', 400);
                return;
            }
        } else {
            $monthlySalary = (float) ($input['monthly_salary'] ?? 0);
            if ($monthlySalary <= 0) {
                ResponseHelper::error('Monthly salary must be greater than 0.', 400);
                return;
            }
        }

        $overtimeMultiplier = (float) ($input['overtime_multiplier'] ?? 1.5);
        if ($overtimeMultiplier < 1) {
            ResponseHelper::error('Overtime multiplier must be at least 1.', 400);
            return;
        }

        $compensation = Compensation::create([
            'user_id' => $input['user_id'],
            'compensation_type' => $type,
            'hourly_rate' => $hourlyRate,
            'monthly_salary' => $monthlySalary,
            'overtime_multiplier' => $overtimeMultiplier,
            'currency' => trim($input['currency'] ?? 'ZAR'),
            'effective_date' => $input['effective_date'],
            'created_by' => $user['id'] ?? null,
        ]);

        ResponseHelper::success(['compensation' => $compensation], 'Compensation saved.');
    }

    /** GET /api/payroll/report?user_id=&period_type=WEEK|MONTH|YEAR&year=&month=&week= */
    public function report(): void {
        $user = $this->requireAdmin();
        if (!$user) return;

        $userId = trim($_GET['user_id'] ?? '');
        $periodType = strtoupper(trim($_GET['period_type'] ?? 'MONTH'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = isset($_GET['month']) && $_GET['month'] !== '' ? (int) $_GET['month'] : null;
        $week = isset($_GET['week']) && $_GET['week'] !== '' ? (int) $_GET['week'] : null;

        if (!$userId) {
            ResponseHelper::error('user_id is required.', 400);
            return;
        }
        if (!in_array($periodType, ['WEEK', 'MONTH', 'YEAR'], true)) {
            ResponseHelper::error('period_type must be WEEK, MONTH or YEAR.', 400);
            return;
        }
        if (!User::find($userId)) {
            ResponseHelper::error('Staff member not found.', 404);
            return;
        }

        $service = new PayrollService();
        [$periodStart, $periodEnd] = $service->resolvePeriodRange($periodType, $year, $month, $week);

        if ($periodStart > date('Y-m-d')) {
            ResponseHelper::error('Cannot generate a payroll report for a future period.', 400);
            return;
        }
        // Cap an in-progress period to "today" so we never read future attendance.
        if ($periodEnd > date('Y-m-d')) {
            $periodEnd = date('Y-m-d');
        }

        $result = $service->generateForPeriod($userId, $periodType, $periodStart, $periodEnd, $user['id'] ?? null);

        ResponseHelper::success(['report' => $result]);
    }

    /** GET /api/payroll/history?user_id=STF-1001 — previously generated reports */
    public function history(): void {
        $user = $this->requireAdmin();
        if (!$user) return;

        $userId = trim($_GET['user_id'] ?? '');
        if (!$userId) {
            ResponseHelper::error('user_id is required.', 400);
            return;
        }

        ResponseHelper::success(['records' => PayrollRecord::forUser($userId)]);
    }
}