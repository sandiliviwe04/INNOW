<?php
$pageTitle = "Attendance Audit Logs — INNOW";
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
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Attendance Audit Trail</h1>
            <p class="text-xs text-zinc-500">Immutable history of check-ins, check-outs, breaks, and MySQL database records</p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <button onclick="exportCSV()" class="px-4 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer border border-zinc-200">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export CSV</span>
            </button>
            <button onclick="openManualModal()" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2 cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Manual Admin Entry</span>
            </button>
        </div>
    </div>

    <!-- Audit Table -->
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs" id="logs-table">
                <thead class="bg-zinc-50 text-zinc-500 uppercase font-mono border-b border-zinc-200">
                    <tr>
                        <th class="py-3 px-4">Log ID</th>
                        <th class="py-3 px-4">Staff Member</th>
                        <th class="py-3 px-4">Event Action</th>
                        <th class="py-3 px-4">Check-In Method</th>
                        <th class="py-3 px-4">Timestamp (SAST)</th>
                        <th class="py-3 px-4">Storage Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    <?php foreach ($allLogs as $log): ?>
                        <tr class="hover:bg-zinc-50 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-zinc-900"><?= htmlspecialchars($log['id']) ?></td>
                            <td class="py-3.5 px-4">
                                <p class="font-extrabold text-zinc-900 text-sm"><?= htmlspecialchars($log['staff_name']) ?></p>
                                <p class="text-[11px] text-zinc-500"><?= htmlspecialchars($log['department']) ?></p>
                            </td>
                            <td class="py-3.5 px-4">
                                <?php if ($log['action'] === 'CLOCK_IN'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">CLOCK IN</span>
                                <?php elseif ($log['action'] === 'CLOCK_OUT'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-700 border border-zinc-200">CLOCK OUT</span>
                                <?php elseif ($log['action'] === 'BREAK_START'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">BREAK START</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200"><?= htmlspecialchars($log['action']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <span class="px-2 py-0.5 rounded bg-zinc-100 text-zinc-800 font-bold border border-zinc-200">
                                    <?= htmlspecialchars($log['method']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-zinc-700"><?= htmlspecialchars($log['timestamp']) ?></td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 w-max">
                                    <i data-lucide="database" class="w-3 h-3"></i> STORED (MySQL)
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Manual Entry Modal -->
<div id="manual-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white border border-zinc-200 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-6">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
            <h3 class="text-lg font-bold text-zinc-900">Manual Attendance Entry</h3>
            <button onclick="closeManualModal()" class="p-1 hover:bg-zinc-100 rounded-lg text-zinc-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form onsubmit="handleManualEntry(event)" class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Select Staff Member</label>
                <select id="manual-user-id" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                    <?php foreach ($allStaff as $stf): ?>
                        <option value="<?= htmlspecialchars($stf['id']) ?>"><?= htmlspecialchars($stf['name']) ?> (<?= htmlspecialchars($stf['department']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Action Type</label>
                <select id="manual-action" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                    <option value="CLOCK_IN">CLOCK IN</option>
                    <option value="CLOCK_OUT">CLOCK OUT</option>
                    <option value="BREAK_START">BREAK START</option>
                    <option value="BREAK_END">BREAK END</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Supervisor Notes</label>
                <input type="text" id="manual-notes" placeholder="e.g. Manual override due to badge loss" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all cursor-pointer">
                Submit Manual Record
            </button>
        </form>
    </div>
</div>

<script>
    function openManualModal() { document.getElementById('manual-modal').classList.replace('hidden', 'flex'); }
    function closeManualModal() { document.getElementById('manual-modal').classList.replace('flex', 'hidden'); }

    async function handleManualEntry(e) {
        e.preventDefault();
        const body = {
            user_id: document.getElementById('manual-user-id').value,
            action: document.getElementById('manual-action').value,
            notes: document.getElementById('manual-notes').value,
        };

        try {
            const res = await authFetch('/api/manual-entry', {
                method: 'POST',
                body: JSON.stringify(body)
            });
            window.location.reload();
        } catch (err) {
            alert('Failed to submit manual record.');
        }
    }

    function exportCSV() {
        let csv = [];
        const rows = document.querySelectorAll('#logs-table tr');
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 0; j < cols.length; j++) row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
            csv.push(row.join(','));
        }
        const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        const downloadLink = document.createElement('a');
        downloadLink.download = 'INNOW_Attendance_Audit_Log.csv';
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.click();
    }

    async function refreshLogs() {
        try {
            const res = await authFetch('/api/dashboard/summary');
            const data = await res.json();
            if (!data.success) return;
            const logs = (data.recent_logs || []).slice(0, 50);
            const tbody = document.querySelector('#logs-table tbody');
            if (!tbody) return;
            tbody.innerHTML = logs.map(log => {
                let actionBadge = '';
                if (log.action === 'CLOCK_IN') actionBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">CLOCK IN</span>';
                else if (log.action === 'CLOCK_OUT') actionBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-700 border border-zinc-200">CLOCK OUT</span>';
                else if (log.action === 'BREAK_START') actionBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">BREAK START</span>';
                else actionBadge = `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">${log.action}</span>`;
                return `<tr class="hover:bg-zinc-50 transition-colors">
                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900">${log.id}</td>
                    <td class="py-3.5 px-4">
                        <p class="font-extrabold text-zinc-900 text-sm">${log.staff_name || ''}</p>
                        <p class="text-[11px] text-zinc-500">${log.department || ''}</p>
                    </td>
                    <td class="py-3.5 px-4">${actionBadge}</td>
                    <td class="py-3.5 px-4 font-mono">
                        <span class="px-2 py-0.5 rounded bg-zinc-100 text-zinc-800 font-bold border border-zinc-200">
                            ${log.method}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 font-mono text-zinc-700">${log.timestamp || ''}</td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 w-max">
                            <i data-lucide="database" class="w-3 h-3"></i> STORED (MySQL)
                        </span>
                    </td>
                </tr>`;
            }).join('');
            if (window.lucide) lucide.createIcons();
        } catch (e) {
            console.error('Failed to refresh logs:', e);
        }
    }

    setInterval(refreshLogs, 30000);
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
