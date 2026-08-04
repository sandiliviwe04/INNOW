<?php
$pageTitle = "Check-In — INNOW";
$user = $user ?? null;
$isAdmin = $user && (stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator');
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
    <!-- Header Banner -->
    <div class="bg-zinc-900 text-white rounded-2xl p-6 sm:p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
        <div class="space-y-2 z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-bold uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                <span>Active Front Gate Scanner</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Digital Attendance Terminal</h1>
            <p class="text-zinc-400 text-xs sm:text-sm max-w-xl">
                Scan your signed 30-second live QR code or use the one-click instant button to record attendance directly.
            </p>
        </div>

        <div class="flex items-center gap-3 z-10">
            <div class="bg-zinc-800/80 border border-zinc-700 px-4 py-2.5 rounded-xl text-center">
                <span class="text-[10px] text-zinc-400 block uppercase font-mono">Current SAST Time</span>
                <span id="checkin-time-display" class="text-xl font-black font-mono text-white"></span>
            </div>
        </div>
    </div>

    <!-- Alert / Toast Container -->
    <div id="checkin-toast" class="hidden p-4 rounded-xl border font-semibold text-sm transition-all duration-300 shadow-md"></div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <?php if ($isAdmin): ?>
        <!-- LEFT COLUMN: Live QR Code Scanner Display (Admin Only) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-xs flex flex-col items-center justify-center text-center space-y-6">
            <div class="flex items-center justify-between w-full border-b border-zinc-100 pb-4">
                <div class="text-left">
                    <h2 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                        <i data-lucide="qr-code" class="w-5 h-5 text-red-600"></i>
                        <span>Signed Short-Lived QR Code</span>
                    </h2>
                    <p class="text-xs text-zinc-500">Regenerates every 30 seconds with HMAC signature security</p>
                </div>
                <span id="qr-timer-badge" class="px-3 py-1 bg-red-50 text-red-700 border border-red-200 rounded-full text-xs font-bold font-mono">
                    30s
                </span>
            </div>

            <!-- QR Code Display Box -->
            <div class="relative bg-zinc-50 border-2 border-dashed border-zinc-300 p-6 rounded-2xl flex flex-col items-center justify-center max-w-xs w-full shadow-inner">
                <div id="qr-code-canvas" class="p-4 bg-white rounded-xl shadow-xs border border-zinc-200">
                    <!-- SVG QR Code rendered via JavaScript -->
                </div>

                <div class="mt-4 text-center space-y-1">
                    <p class="text-[11px] font-mono font-bold text-zinc-700" id="qr-token-preview">INNOW-HMAC-SIGNATURE-SYNCING...</p>
                    <p class="text-[10px] text-zinc-400 font-mono uppercase">Terminal ID: TRM-MAIN-GATE-01</p>
                </div>
            </div>

            <!-- Quick Action Buttons under QR -->
            <div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <button onclick="refreshQRPayload()" class="py-2.5 px-4 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4 text-zinc-600"></i>
                    <span>Force Refresh QR Code</span>
                </button>
                <button onclick="simulateCameraScan()" class="py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                    <span>Scan QR to Clock In/Out</span>
                </button>
            </div>
        </div>
        <?php else: ?>
        <!-- LEFT COLUMN: QR Scanner Link for Non-Admins -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-xs flex flex-col items-center justify-center text-center space-y-6">
            <div class="bg-zinc-50 border-2 border-dashed border-zinc-300 p-8 rounded-2xl flex flex-col items-center justify-center max-w-sm w-full">
                <i data-lucide="qr-code" class="w-12 h-12 text-zinc-300 mb-4"></i>
                <p class="text-sm font-bold text-zinc-500">Scan QR to Check In/Out</p>
                <p class="text-xs text-zinc-400 mt-1">Use your phone camera to scan the reception QR code.</p>
                <a href="/checkin/qr" class="mt-4 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-colors inline-flex items-center gap-2">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                    <span>Open QR Scanner</span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- RIGHT COLUMN: One-Click Button Check-In & Staff Selector -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="border-b border-zinc-100 pb-4">
                <h2 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                    <i data-lucide="mouse-pointer-click" class="w-5 h-5 text-red-600"></i>
                    <span>Simple One-Click Button</span>
                </h2>
                <p class="text-xs text-zinc-500">Instant direct check-in/out without needing a mobile camera</p>
            </div>

            <!-- Staff Selection Dropdown -->
            <div class="space-y-2">
                <label for="staff-selector" class="block text-xs font-bold text-zinc-700 uppercase tracking-wider">Select Staff Member</label>
                <select id="staff-selector" onchange="onStaffSelected()" class="w-full px-4 py-3 border border-zinc-300 rounded-xl text-sm font-semibold text-zinc-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    <?php foreach ($allStaff as $stf): ?>
                        <option value="<?= htmlspecialchars($stf['id']) ?>" data-status="<?= $stf['status'] ?>" data-name="<?= htmlspecialchars($stf['name']) ?>" data-role="<?= htmlspecialchars($stf['role']) ?>" data-dept="<?= htmlspecialchars($stf['department']) ?>">
                            <?= htmlspecialchars($stf['name']) ?> (<?= htmlspecialchars($stf['department']) ?>) — Currently <?= $stf['status'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Selected Staff Card Preview -->
            <div id="selected-staff-card" class="bg-zinc-50 p-4 rounded-xl border border-zinc-200 flex items-center gap-4">
                <div id="staff-card-avatar" class="w-12 h-12 rounded-xl bg-zinc-900 text-white flex items-center justify-center text-sm font-bold shrink-0">
                    A
                </div>
                <div class="flex-1">
                    <h3 id="staff-card-name" class="font-extrabold text-sm text-zinc-900">Admin Supervisor</h3>
                    <p id="staff-card-dept" class="text-xs text-zinc-500">Operations & IT • System Administrator</p>
                    <span id="staff-card-badge" class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                        ONSITE
                    </span>
                </div>
            </div>

            <!-- One-Click Primary Button -->
            <div class="space-y-3">
                <button id="primary-one-click-btn" onclick="triggerOneClickCheckin()" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-base transition-all shadow-md flex items-center justify-center gap-3 cursor-pointer">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    <span id="one-click-btn-text">ONE-CLICK CHECK-IN</span>
                </button>

                <div class="grid grid-cols-2 gap-2">
                    <button onclick="triggerOneClickCheckin('BREAK_START')" class="py-2.5 px-3 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="coffee" class="w-4 h-4"></i>
                        <span>Start Break</span>
                    </button>
                    <button onclick="triggerOneClickCheckin('CLOCK_OUT')" class="py-2.5 px-3 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 border border-zinc-200 rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Clock Out</span>
                    </button>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-900 flex items-start gap-2">
                <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5"></i>
                <p>Every check-in event triggers real-time verification and stores directly in MySQL.</p>
            </div>
        </div>
    </div>
</main>

<!-- QRCode Generator JS -->
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
    let activeQRPayload = null;
    let timerCountdown = 30;
    let timerInterval = null;

    function updateCheckinClock() {
        const el = document.getElementById('checkin-time-display');
        if (el) {
            el.innerText = new Date().toLocaleTimeString('en-ZA');
        }
    }
    setInterval(updateCheckinClock, 1000);
    updateCheckinClock();

    async function refreshQRPayload() {
        try {
            const res = await authFetch('/api/checkin/qr-payload');
            const data = await res.json();
            if (data.success) {
                activeQRPayload = data.payload;
                renderQRCode(activeQRPayload.scan_url || activeQRPayload.token);
                timerCountdown = activeQRPayload.ttl_seconds || 30;
                document.getElementById('qr-token-preview').innerText = 'SCAN ME: ' + (activeQRPayload.scan_url || activeQRPayload.token).substring(0, 40) + '...';
            }
        } catch (err) {
            console.error('Failed to fetch QR payload:', err);
        }
    }

    function renderQRCode(text) {
        const qr = qrcode(0, 'M');
        qr.addData(text);
        qr.make();
        document.getElementById('qr-code-canvas').innerHTML = qr.createSvgTag({ scalen: 5, margin: 1 });
    }

    function startTimer() {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            timerCountdown--;
            const badge = document.getElementById('qr-timer-badge');
            if (badge) badge.innerText = timerCountdown + 's';
            if (timerCountdown <= 0) {
                refreshQRPayload();
            }
        }, 1000);
    }

    function onStaffSelected() {
        const select = document.getElementById('staff-selector');
        const opt = select.options[select.selectedIndex];
        const status = opt.getAttribute('data-status');
        const name = opt.getAttribute('data-name');
        const avatar = opt.getAttribute('data-avatar');
        const dept = opt.getAttribute('data-dept');
        const role = opt.getAttribute('data-role');

        document.getElementById('staff-card-name').innerText = name;
        document.getElementById('staff-card-dept').innerText = dept + ' • ' + role;
        document.getElementById('staff-card-avatar').innerText = name ? name.charAt(0).toUpperCase() : 'U';

        const badge = document.getElementById('staff-card-badge');
        const mainBtn = document.getElementById('primary-one-click-btn');
        const mainBtnText = document.getElementById('one-click-btn-text');

        if (status === 'ONSITE') {
            badge.innerText = 'ONSITE';
            badge.className = 'inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200';
            mainBtn.className = 'w-full py-4 bg-red-600 hover:bg-red-700 text-white font-black rounded-xl text-base transition-all shadow-md flex items-center justify-center gap-3 cursor-pointer';
            mainBtnText.innerText = 'ONE-CLICK CLOCK-OUT';
        } else if (status === 'BREAK') {
            badge.innerText = 'ON BREAK';
            badge.className = 'inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-200';
            mainBtn.className = 'w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-base transition-all shadow-md flex items-center justify-center gap-3 cursor-pointer';
            mainBtnText.innerText = 'END BREAK & CHECK-IN';
        } else {
            badge.innerText = 'OFFSITE';
            badge.className = 'inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-zinc-100 text-zinc-600 border border-zinc-200';
            mainBtn.className = 'w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-base transition-all shadow-md flex items-center justify-center gap-3 cursor-pointer';
            mainBtnText.innerText = 'ONE-CLICK CHECK-IN';
        }
    }

    async function triggerOneClickCheckin(customAction = null) {
        const select = document.getElementById('staff-selector');
        const userId = select.value;

        try {
            const res = await authFetch('/api/checkin/button', {
                method: 'POST',
                body: JSON.stringify({ user_id: userId, action: customAction })
            });
            const data = await res.json();
            showToast(data);
            if (data.success) {
                await refreshStaffState();
            }
        } catch (err) {
            showToast({ success: false, message: 'Check-in request failed.' });
        }
    }

    async function simulateCameraScan() {
        if (!activeQRPayload) return;
        const select = document.getElementById('staff-selector');
        const userId = select.value;

        try {
            const res = await authFetch('/api/checkin/qr', {
                method: 'POST',
                body: JSON.stringify({ qr_token: activeQRPayload.token, user_id: userId })
            });
            const data = await res.json();
            showToast(data);
            if (data.success) {
                await refreshStaffState();
            }
        } catch (err) {
            showToast({ success: false, message: 'QR Scan verification failed.' });
        }
    }

    async function refreshStaffState() {
        try {
            const res = await authFetch('/api/dashboard/summary');
            const data = await res.json();
            if (!data.success) return;
            const staff = data.all_staff || [];
            const staffMap = new Map(staff.map(s => [s.id, s]));
            const select = document.getElementById('staff-selector');
            if (!select) return;

            Array.from(select.options).forEach(opt => {
                const s = staffMap.get(opt.value);
                if (!s) return;
                opt.setAttribute('data-status', s.status);
                opt.setAttribute('data-name', s.name);
                opt.setAttribute('data-role', s.role);
                opt.setAttribute('data-dept', s.department);
                opt.text = `${s.name} (${s.department}) — Currently ${s.status}`;
            });

            const currentId = select.value;
            if (currentId) {
                const opt = select.querySelector(`option[value="${currentId}"]`);
                if (opt) {
                    opt.selected = true;
                    onStaffSelected();
                }
            }
        } catch (e) {
            console.error('Failed to refresh staff state:', e);
        }
    }

    setInterval(refreshStaffState, 30000);

    function showToast(data) {
        const toast = document.getElementById('checkin-toast');
        toast.classList.remove('hidden');
        const errMsg = (data && data.message) ? data.message : ((data && data.error) ? data.error : 'Verification failed.');
        if (data && data.success) {
            toast.className = 'p-4 rounded-xl border font-semibold text-sm transition-all duration-300 shadow-md bg-emerald-50 border-emerald-200 text-emerald-900';
            toast.innerHTML = `<div class="flex items-center gap-2"><i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i><span>SUCCESS: ${data.action || 'Action'} for ${data.user ? data.user.name : 'Staff'} recorded & saved to MySQL database!</span></div>`;
        } else {
            toast.className = 'p-4 rounded-xl border font-semibold text-sm transition-all duration-300 shadow-md bg-red-50 border-red-200 text-red-900';
            toast.innerHTML = `<div class="flex items-center gap-2"><i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i><span>ERROR: ${errMsg}</span></div>`;
        }
        if (window.lucide) lucide.createIcons();
    }

    <?php if ($isAdmin): ?>
    refreshQRPayload();
    startTimer();
    <?php endif; ?>
    onStaffSelected();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
