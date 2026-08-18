<?php
$pageTitle = "Live Onsite Dashboard — INNOW";
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/nav.php';

$onsiteCount = count(array_filter($allStaff, fn($s) => $s['status'] === 'ONSITE'));
$breakCount = count(array_filter($allStaff, fn($s) => $s['status'] === 'BREAK'));
$offsiteCount = count(array_filter($allStaff, fn($s) => $s['status'] === 'OFFSITE'));
$isAdmin = $user && (stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator');
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Live Onsite Personnel</h1>
            <p class="text-xs text-zinc-500">Real-time facility occupancy monitoring & staff attendance tracking</p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="/checkin" class="px-4 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
                <span>Open Attendance Scanner</span>
            </a>
        </div>
    </div>

        <!-- Summary Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-zinc-500">
                    <span class="text-xs font-bold uppercase tracking-wider">Total Onsite</span>
                    <i data-lucide="building-2" class="w-4 h-4 text-emerald-600"></i>
                </div>
                <p class="text-3xl font-black text-zinc-900" id="metric-onsite"><?= $onsiteCount ?></p>
                <p class="text-[11px] text-emerald-700 font-medium">Inside INNOW facility</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-zinc-500">
                    <span class="text-xs font-bold uppercase tracking-wider">On Break</span>
                    <i data-lucide="coffee" class="w-4 h-4 text-amber-600"></i>
                </div>
                <p class="text-3xl font-black text-amber-600" id="metric-break"><?= $breakCount ?></p>
                <p class="text-[11px] text-amber-700 font-medium">Temporary break duration</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-zinc-500">
                    <span class="text-xs font-bold uppercase tracking-wider">Offsite / Clocked Out</span>
                    <i data-lucide="user-minus" class="w-4 h-4 text-zinc-400"></i>
                </div>
                <p class="text-3xl font-black text-zinc-600" id="metric-offsite"><?= $offsiteCount ?></p>
                <p class="text-[11px] text-zinc-500 font-medium">Outside building premises</p>
            </div>
        </div>

    <!-- Main Staff Roster -->
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-zinc-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <h2 class="text-base font-bold text-zinc-900 flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5 text-red-600"></i>
                <span>Active Personnel Roster</span>
            </h2>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <input type="text" id="roster-search" onkeyup="filterRoster()" placeholder="Search staff by name..." class="px-3 py-1.5 border border-zinc-300 rounded-xl text-xs outline-none focus:ring-2 focus:ring-red-500 w-full sm:w-64">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50 text-zinc-500 uppercase font-mono border-b border-zinc-200">
                    <tr>
                        <th class="py-3 px-4">Staff Member</th>
                        <th class="py-3 px-4">Department & Role</th>
                        <th class="py-3 px-4">Contact & ICE</th>
                        <th class="py-3 px-4">Status</th>
                        <?php if ($isAdmin): ?>
                        <th class="py-3 px-4 text-right">Quick Toggle</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200" id="roster-tbody">
                    <?php foreach ($allStaff as $stf): ?>
                        <tr class="hover:bg-zinc-50/80 transition-colors roster-row" data-name="<?= strtolower(htmlspecialchars($stf['name'])) ?>" data-user-id="<?= htmlspecialchars($stf['id']) ?>">
                            <td class="py-3.5 px-4 font-bold text-zinc-900 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-zinc-900 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                        <?= strtoupper(substr($stf['name'], 0, 1)) ?>
                                    </div>
                                <div>
                                    <p class="font-extrabold text-sm text-zinc-900"><?= htmlspecialchars($stf['name']) ?></p>
                                    <p class="text-[10px] font-mono text-zinc-400"><?= htmlspecialchars($stf['id']) ?></p>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-zinc-800"><?= htmlspecialchars($stf['department']) ?></p>
                                <p class="text-[11px] text-zinc-500"><?= htmlspecialchars($stf['role']) ?></p>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <p class="text-zinc-800 font-bold"><?= htmlspecialchars($stf['phone']) ?></p>
                                <p class="text-[10px] text-zinc-400"><?= htmlspecialchars($stf['emergency_contact'] ?: 'N/A') ?></p>
                            </td>
                            <td class="py-3.5 px-4 status-cell">
                                <?php if ($stf['status'] === 'ONSITE'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> ONSITE
                                    </span>
                                <?php elseif ($stf['status'] === 'BREAK'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> ON BREAK
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-600 border border-zinc-200 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span> OFFSITE
                                    </span>
                                <?php endif; ?>
                            </td>
                            <?php if ($isAdmin): ?>
                            <td class="py-3.5 px-4 text-right">
                                <button onclick="quickToggleStatus('<?= $stf['id'] ?>')" class="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-lg text-[11px] font-bold cursor-pointer transition-colors shadow-xs">
                                    Toggle State
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    function filterRoster() {
        const q = document.getElementById('roster-search').value.toLowerCase();
        const rows = document.querySelectorAll('.roster-row');
        rows.forEach(r => {
            const name = r.getAttribute('data-name');
            r.style.display = name.includes(q) ? '' : 'none';
        });
    }

    async function quickToggleStatus(userId) {
        try {
            const res = await authFetch('/api/checkin/button', {
                method: 'POST',
                body: JSON.stringify({ user_id: userId })
            });
            const data = await res.json();
            if (data.success) {
                await refreshDashboard();
            } else {
                alert(data.message || 'Failed to toggle status.');
            }
        } catch (e) {
            alert('Failed to toggle status.');
        }
    }

    async function refreshDashboard() {
        try {
            const res = await authFetch('/api/dashboard/summary');
            const data = await res.json();
            if (!data.success) return;
            const m = data.metrics || {};
            const onsiteEl = document.getElementById('metric-onsite');
            const breakEl = document.getElementById('metric-break');
            const offsiteEl = document.getElementById('metric-offsite');
            if (onsiteEl) onsiteEl.innerText = m.onsite_count ?? onsiteEl.innerText;
            if (breakEl) breakEl.innerText = m.break_count ?? breakEl.innerText;
            if (offsiteEl) offsiteEl.innerText = m.offsite_count ?? offsiteEl.innerText;

            const staffMap = new Map((data.all_staff || []).map(s => [s.id, s.status]));
            document.querySelectorAll('.roster-row').forEach(row => {
                const uid = row.getAttribute('data-user-id');
                const status = staffMap.get(uid);
                if (!status) return;
                const cell = row.querySelector('.status-cell');
                if (!cell) return;
                const dotColor = status === 'ONSITE' ? 'bg-emerald-600' : (status === 'BREAK' ? 'bg-amber-600' : 'bg-zinc-400');
                const badgeClass = status === 'ONSITE' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : (status === 'BREAK' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-zinc-100 text-zinc-600 border border-zinc-200');
                cell.innerHTML = `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold ${badgeClass} inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full ${dotColor}"></span> ${status.replace('_', ' ')}
                </span>`;
            });
        } catch (e) {
            console.error('Dashboard refresh failed:', e);
        }
    }

    setInterval(refreshDashboard, 30000);
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>