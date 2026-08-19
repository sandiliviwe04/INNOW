<?php
$pageTitle = "Payroll & Hours — INNOW";
$user = $user ?? null;
$isAdmin = $user && (stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator');
if (!$isAdmin) {
    header('Location: /dashboard');
    exit;
}
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Payroll &amp; Hours</h1>
            <p class="text-xs text-zinc-500">Configure working hours, compensation, and view payroll reports</p>
        </div>
        <div class="w-full sm:w-72">
            <label class="block font-bold text-zinc-700 uppercase text-[10px] mb-1">Employee</label>
            <select id="payroll-employee-select" onchange="onEmployeeChange()" class="w-full px-3 py-2.5 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500 text-sm font-semibold bg-white">
                <option value="">Select a staff member…</option>
                <?php foreach ($allStaff as $staff): ?>
                    <option value="<?= htmlspecialchars($staff['id']) ?>"><?= htmlspecialchars($staff['name']) ?> — <?= htmlspecialchars($staff['department']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="payroll-empty-state" class="bg-white rounded-2xl border border-zinc-200 shadow-xs p-10 text-center text-xs text-zinc-400">
        Select an employee above to configure their working hours, compensation, and view payroll reports.
    </div>

    <div id="payroll-content" class="hidden space-y-8">

        <!-- Working Hours -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-zinc-900">Working Hours Schedule</h2>
                <span class="text-[10px] text-zinc-400">New entries take effect from the chosen date — history is preserved</span>
            </div>
            <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <form onsubmit="submitSchedule(event)" class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 uppercase mb-1">Hours / Day</label>
                            <input type="number" step="0.25" min="0.25" max="24" id="sch-hours-per-day" required class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block font-bold text-zinc-700 uppercase mb-1">Hours / Week</label>
                            <input type="number" step="0.25" min="0.25" max="168" id="sch-hours-per-week" required class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 uppercase mb-1">Start Time</label>
                            <input type="time" id="sch-start-time" required class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block font-bold text-zinc-700 uppercase mb-1">End Time</label>
                            <input type="time" id="sch-end-time" required class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-zinc-700 uppercase mb-1">Working Days</label>
                        <div class="flex flex-wrap gap-2">
                            <?php $dayLabels = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun']; ?>
                            <?php foreach ($dayLabels as $num => $label): ?>
                                <label class="flex items-center gap-1.5 px-2.5 py-1.5 border border-zinc-300 rounded-lg cursor-pointer has-[:checked]:bg-red-50 has-[:checked]:border-red-400">
                                    <input type="checkbox" class="sch-working-day" value="<?= $num ?>" <?= $num <= 5 ? 'checked' : '' ?>>
                                    <span class="font-semibold"><?= $label ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-zinc-700 uppercase mb-1">Break Duration (min)</label>
                            <input type="number" min="0" max="480" value="60" id="sch-break-minutes" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block font-bold text-zinc-700 uppercase mb-1">Break Type</label>
                            <select id="sch-break-paid" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                                <option value="0">Unpaid</option>
                                <option value="1">Paid</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-zinc-700 uppercase mb-1">Effective Date</label>
                        <input type="date" id="sch-effective-date" required class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all cursor-pointer">Save Schedule Version</button>
                </form>

                <div>
                    <p class="font-bold text-zinc-700 uppercase text-[10px] mb-2">History</p>
                    <div class="overflow-x-auto max-h-72 overflow-y-auto border border-zinc-100 rounded-xl">
                        <table class="w-full text-left text-[11px]">
                            <thead class="bg-zinc-50 text-zinc-500 uppercase font-mono sticky top-0">
                                <tr>
                                    <th class="py-2 px-3">Effective</th>
                                    <th class="py-2 px-3">Hrs/Day</th>
                                    <th class="py-2 px-3">Hrs/Week</th>
                                    <th class="py-2 px-3">Days</th>
                                    <th class="py-2 px-3">Break</th>
                                </tr>
                            </thead>
                            <tbody id="schedule-history-tbody" class="divide-y divide-zinc-100">
                                <tr><td colspan="5" class="py-4 text-center text-zinc-400">No schedule configured yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compensation -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-zinc-900">Compensation</h2>
                <span class="text-[10px] text-zinc-400">New entries take effect from the chosen date — history is preserved</span>
            </div>
            <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <form onsubmit="submitCompensation(event)" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-zinc-700 uppercase mb-1">Pay Type</label>
                        <select id="comp-type" onchange="toggleCompensationFields()" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                            <option value="HOURLY">Hourly</option>
                            <option value="MONTHLY">Monthly</option>
                        </select>
                    </div>
                    <div id="comp-hourly-field">
                        <label class="block font-bold text-zinc-700 uppercase mb-1">Hourly Rate (ZAR)</label>
                        <input type="number" step="0.01" min="0.01" id="comp-hourly-rate" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div id="comp-monthly-field" class="hidden">
                        <label class="block font-bold text-zinc-700 uppercase mb-1">Monthly Salary (ZAR)</label>
                        <input type="number" step="0.01" min="0.01" id="comp-monthly-salary" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block font-bold text-zinc-700 uppercase mb-1">Overtime Multiplier</label>
                        <input type="number" step="0.1" min="1" value="1.5" id="comp-overtime-multiplier" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block font-bold text-zinc-700 uppercase mb-1">Effective Date</label>
                        <input type="date" id="comp-effective-date" required class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all cursor-pointer">Save Compensation Version</button>
                </form>

                <div>
                    <p class="font-bold text-zinc-700 uppercase text-[10px] mb-2">History</p>
                    <div class="overflow-x-auto max-h-72 overflow-y-auto border border-zinc-100 rounded-xl">
                        <table class="w-full text-left text-[11px]">
                            <thead class="bg-zinc-50 text-zinc-500 uppercase font-mono sticky top-0">
                                <tr>
                                    <th class="py-2 px-3">Effective</th>
                                    <th class="py-2 px-3">Type</th>
                                    <th class="py-2 px-3">Rate</th>
                                    <th class="py-2 px-3">OT ×</th>
                                </tr>
                            </thead>
                            <tbody id="compensation-history-tbody" class="divide-y divide-zinc-100">
                                <tr><td colspan="4" class="py-4 text-center text-zinc-400">No compensation configured yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Report -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100">
                <h2 class="text-sm font-extrabold text-zinc-900">Payroll Report</h2>
            </div>
            <div class="p-5 space-y-5">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block font-bold text-zinc-700 uppercase text-[10px] mb-1">Period</label>
                        <select id="report-period-type" onchange="onPeriodTypeChange()" class="px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500 text-xs font-semibold">
                            <option value="WEEK">Week</option>
                            <option value="MONTH" selected>Month</option>
                            <option value="YEAR">Year</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-zinc-700 uppercase text-[10px] mb-1">Year</label>
                        <input type="number" id="report-year" class="w-24 px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500 text-xs font-semibold">
                    </div>
                    <div id="report-month-field">
                        <label class="block font-bold text-zinc-700 uppercase text-[10px] mb-1">Month</label>
                        <select id="report-month" class="px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500 text-xs font-semibold">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>"><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div id="report-week-field" class="hidden">
                        <label class="block font-bold text-zinc-700 uppercase text-[10px] mb-1">ISO Week #</label>
                        <input type="number" min="1" max="53" id="report-week" class="w-20 px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500 text-xs font-semibold">
                    </div>
                    <button onclick="generateReport()" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-all shadow-md cursor-pointer">Generate</button>
                </div>

                <div id="report-error" class="hidden text-xs text-red-600 font-semibold"></div>

                <div id="report-result" class="hidden grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                        <p class="text-[10px] font-bold text-zinc-500 uppercase">Expected Hours</p>
                        <p id="res-expected" class="text-xl font-extrabold text-zinc-900 font-mono">-</p>
                    </div>
                    <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                        <p class="text-[10px] font-bold text-zinc-500 uppercase">Actual Hours</p>
                        <p id="res-actual" class="text-xl font-extrabold text-zinc-900 font-mono">-</p>
                    </div>
                    <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                        <p class="text-[10px] font-bold text-zinc-500 uppercase">Regular Hours</p>
                        <p id="res-regular" class="text-xl font-extrabold text-zinc-900 font-mono">-</p>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                        <p class="text-[10px] font-bold text-amber-700 uppercase">Overtime Hours</p>
                        <p id="res-overtime" class="text-xl font-extrabold text-amber-800 font-mono">-</p>
                    </div>
                    <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                        <p class="text-[10px] font-bold text-zinc-500 uppercase">Pay Type / Rate</p>
                        <p id="res-rate" class="text-sm font-extrabold text-zinc-900 font-mono">-</p>
                    </div>
                    <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                        <p class="text-[10px] font-bold text-zinc-500 uppercase">Regular Pay</p>
                        <p id="res-regular-pay" class="text-lg font-extrabold text-zinc-900 font-mono">-</p>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                        <p class="text-[10px] font-bold text-amber-700 uppercase">Overtime Pay</p>
                        <p id="res-overtime-pay" class="text-lg font-extrabold text-amber-800 font-mono">-</p>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                        <p class="text-[10px] font-bold text-emerald-700 uppercase">Total Pay</p>
                        <p id="res-total-pay" class="text-lg font-extrabold text-emerald-800 font-mono">-</p>
                    </div>
                </div>

                <div>
                    <p class="font-bold text-zinc-700 uppercase text-[10px] mb-2 mt-2">Previously Generated Reports</p>
                    <div class="overflow-x-auto border border-zinc-100 rounded-xl">
                        <table class="w-full text-left text-[11px]">
                            <thead class="bg-zinc-50 text-zinc-500 uppercase font-mono">
                                <tr>
                                    <th class="py-2 px-3">Period</th>
                                    <th class="py-2 px-3">Range</th>
                                    <th class="py-2 px-3">Regular / OT Hrs</th>
                                    <th class="py-2 px-3">Total Pay</th>
                                    <th class="py-2 px-3">Generated</th>
                                </tr>
                            </thead>
                            <tbody id="report-history-tbody" class="divide-y divide-zinc-100">
                                <tr><td colspan="5" class="py-4 text-center text-zinc-400">No reports generated yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.getElementById('sch-effective-date').valueAsDate = new Date();
    document.getElementById('comp-effective-date').valueAsDate = new Date();
    document.getElementById('report-year').value = new Date().getFullYear();

    function toggleCompensationFields() {
        const type = document.getElementById('comp-type').value;
        document.getElementById('comp-hourly-field').classList.toggle('hidden', type !== 'HOURLY');
        document.getElementById('comp-monthly-field').classList.toggle('hidden', type !== 'MONTHLY');
    }

    function onPeriodTypeChange() {
        const type = document.getElementById('report-period-type').value;
        document.getElementById('report-month-field').classList.toggle('hidden', type !== 'MONTH');
        document.getElementById('report-week-field').classList.toggle('hidden', type !== 'WEEK');
    }

    function currentEmployeeId() {
        return document.getElementById('payroll-employee-select').value;
    }

    function onEmployeeChange() {
        const id = currentEmployeeId();
        document.getElementById('payroll-empty-state').classList.toggle('hidden', !!id);
        document.getElementById('payroll-content').classList.toggle('hidden', !id);
        document.getElementById('report-result').classList.add('hidden');
        if (id) {
            loadSchedules();
            loadCompensation();
            loadReportHistory();
        }
    }

    function fmtHours(v) { return (v === undefined || v === null) ? '-' : Number(v).toFixed(2); }
    function fmtMoney(v) { return (v === undefined || v === null) ? '-' : 'R ' + Number(v).toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    const DAY_LABELS = { 1: 'Mon', 2: 'Tue', 3: 'Wed', 4: 'Thu', 5: 'Fri', 6: 'Sat', 7: 'Sun' };
    function fmtDays(csv) {
        return (csv || '').split(',').filter(Boolean).map(n => DAY_LABELS[parseInt(n, 10)] || n).join(', ');
    }

    async function loadSchedules() {
        const tbody = document.getElementById('schedule-history-tbody');
        try {
            const res = await authFetch('/api/payroll/schedules?user_id=' + encodeURIComponent(currentEmployeeId()));
            const data = await res.json();
            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="5" class="py-4 text-center text-red-600">${data.message}</td></tr>`;
                return;
            }
            const rows = data.schedules || [];
            tbody.innerHTML = rows.length ? rows.map(s => `
                <tr>
                    <td class="py-2 px-3 font-mono">${s.effective_date}</td>
                    <td class="py-2 px-3">${fmtHours(s.hours_per_day)}</td>
                    <td class="py-2 px-3">${fmtHours(s.hours_per_week)}</td>
                    <td class="py-2 px-3">${fmtDays(s.working_days)}</td>
                    <td class="py-2 px-3">${s.break_duration_minutes}min ${s.break_paid == 1 ? '(Paid)' : '(Unpaid)'}</td>
                </tr>
            `).join('') : `<tr><td colspan="5" class="py-4 text-center text-zinc-400">No schedule configured yet.</td></tr>`;
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="5" class="py-4 text-center text-red-600">Failed to load: ${e.message}</td></tr>`;
        }
    }

    async function loadCompensation() {
        const tbody = document.getElementById('compensation-history-tbody');
        try {
            const res = await authFetch('/api/payroll/compensation?user_id=' + encodeURIComponent(currentEmployeeId()));
            const data = await res.json();
            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-red-600">${data.message}</td></tr>`;
                return;
            }
            const rows = data.compensation || [];
            tbody.innerHTML = rows.length ? rows.map(c => `
                <tr>
                    <td class="py-2 px-3 font-mono">${c.effective_date}</td>
                    <td class="py-2 px-3">${c.compensation_type}</td>
                    <td class="py-2 px-3 font-mono">${fmtMoney(c.compensation_type === 'HOURLY' ? c.hourly_rate : c.monthly_salary)}</td>
                    <td class="py-2 px-3">${c.overtime_multiplier}×</td>
                </tr>
            `).join('') : `<tr><td colspan="4" class="py-4 text-center text-zinc-400">No compensation configured yet.</td></tr>`;
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-red-600">Failed to load: ${e.message}</td></tr>`;
        }
    }

    async function submitSchedule(e) {
        e.preventDefault();
        const employeeId = currentEmployeeId();
        if (!employeeId) { alert('Select an employee first.'); return; }

        const workingDays = Array.from(document.querySelectorAll('.sch-working-day:checked')).map(cb => cb.value);
        if (!workingDays.length) { alert('Select at least one working day.'); return; }

        const body = {
            user_id: employeeId,
            hours_per_day: document.getElementById('sch-hours-per-day').value,
            hours_per_week: document.getElementById('sch-hours-per-week').value,
            working_days: workingDays.join(','),
            start_time: document.getElementById('sch-start-time').value,
            end_time: document.getElementById('sch-end-time').value,
            break_duration_minutes: document.getElementById('sch-break-minutes').value,
            break_paid: document.getElementById('sch-break-paid').value === '1',
            effective_date: document.getElementById('sch-effective-date').value,
        };

        try {
            const res = await authFetch('/api/payroll/schedules', { method: 'POST', body: JSON.stringify(body) });
            const data = await res.json();
            if (data.success) {
                loadSchedules();
            } else {
                alert(data.message || 'Failed to save schedule.');
            }
        } catch (e) {
            alert('Error saving schedule: ' + e.message);
        }
    }

    async function submitCompensation(e) {
        e.preventDefault();
        const employeeId = currentEmployeeId();
        if (!employeeId) { alert('Select an employee first.'); return; }

        const type = document.getElementById('comp-type').value;
        const body = {
            user_id: employeeId,
            compensation_type: type,
            hourly_rate: document.getElementById('comp-hourly-rate').value,
            monthly_salary: document.getElementById('comp-monthly-salary').value,
            overtime_multiplier: document.getElementById('comp-overtime-multiplier').value,
            effective_date: document.getElementById('comp-effective-date').value,
        };

        try {
            const res = await authFetch('/api/payroll/compensation', { method: 'POST', body: JSON.stringify(body) });
            const data = await res.json();
            if (data.success) {
                loadCompensation();
            } else {
                alert(data.message || 'Failed to save compensation.');
            }
        } catch (e) {
            alert('Error saving compensation: ' + e.message);
        }
    }

    async function generateReport() {
        const employeeId = currentEmployeeId();
        const errEl = document.getElementById('report-error');
        errEl.classList.add('hidden');
        if (!employeeId) { alert('Select an employee first.'); return; }

        const periodType = document.getElementById('report-period-type').value;
        const year = document.getElementById('report-year').value;
        const params = new URLSearchParams({ user_id: employeeId, period_type: periodType, year });
        if (periodType === 'MONTH') params.set('month', document.getElementById('report-month').value);
        if (periodType === 'WEEK') params.set('week', document.getElementById('report-week').value || '1');

        try {
            const res = await authFetch('/api/payroll/report?' + params.toString());
            const data = await res.json();
            if (!data.success) {
                errEl.textContent = data.message || 'Failed to generate report.';
                errEl.classList.remove('hidden');
                document.getElementById('report-result').classList.add('hidden');
                return;
            }
            const r = data.report;
            document.getElementById('res-expected').textContent = fmtHours(r.expected_hours);
            document.getElementById('res-actual').textContent = fmtHours(r.actual_hours);
            document.getElementById('res-regular').textContent = fmtHours(r.regular_hours);
            document.getElementById('res-overtime').textContent = fmtHours(r.overtime_hours);
            document.getElementById('res-rate').textContent = r.compensation_type
                ? `${r.compensation_type} / ${fmtMoney(r.rate_used)}`
                : 'Not configured';
            document.getElementById('res-regular-pay').textContent = fmtMoney(r.regular_pay);
            document.getElementById('res-overtime-pay').textContent = fmtMoney(r.overtime_pay);
            document.getElementById('res-total-pay').textContent = fmtMoney(r.total_pay);
            document.getElementById('report-result').classList.remove('hidden');
            loadReportHistory();
        } catch (e) {
            errEl.textContent = 'Error generating report: ' + e.message;
            errEl.classList.remove('hidden');
        }
    }

    async function loadReportHistory() {
        const tbody = document.getElementById('report-history-tbody');
        try {
            const res = await authFetch('/api/payroll/history?user_id=' + encodeURIComponent(currentEmployeeId()));
            const data = await res.json();
            if (!data.success) return;
            const rows = data.records || [];
            tbody.innerHTML = rows.length ? rows.map(r => `
                <tr>
                    <td class="py-2 px-3">${r.period_type}</td>
                    <td class="py-2 px-3 font-mono">${r.period_start} → ${r.period_end}</td>
                    <td class="py-2 px-3 font-mono">${fmtHours(r.regular_hours)} / ${fmtHours(r.overtime_hours)}</td>
                    <td class="py-2 px-3 font-mono font-bold">${fmtMoney(r.total_pay)}</td>
                    <td class="py-2 px-3 text-zinc-500">${r.generated_at}</td>
                </tr>
            `).join('') : `<tr><td colspan="5" class="py-4 text-center text-zinc-400">No reports generated yet.</td></tr>`;
        } catch (e) {
            // Non-critical — leave existing content.
        }
    }

    toggleCompensationFields();
    onPeriodTypeChange();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>