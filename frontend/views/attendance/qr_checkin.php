<?php
$pageTitle = "QR Check-In — INNOW";
$user = $user ?? null;
$qrToken = $_GET['token'] ?? '';
$isAdmin = $user && (stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator');
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
    <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-xs space-y-6">
        <div class="text-center">
            <div class="mx-auto w-12 h-12 rounded-xl bg-red-600 flex items-center justify-center text-white shadow-md mb-3">
                <i data-lucide="qr-code" class="w-7 h-7"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-zinc-900 tracking-tight">QR Attendance Scanner</h2>
            <p class="text-xs text-zinc-500 mt-1">Scan the reception QR code to check in or out</p>
        </div>

        <?php if (!$user): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900">
            You need to sign in to use the QR scanner. Enter your email and 4-digit PIN below.
        </div>

        <form id="qr-login-form" onsubmit="handleQRLogin(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1">Email Address</label>
                <input type="email" id="qr-email" required placeholder="you@innow.com" class="w-full px-4 py-3 border border-zinc-300 rounded-xl text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1">4-Digit PIN</label>
                <input type="password" id="qr-pin" maxlength="4" required placeholder="••••" class="w-full px-4 py-3 border border-zinc-300 rounded-xl text-center text-2xl font-mono tracking-widest font-bold focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
            </div>
            <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-md text-sm">
                Sign In
            </button>
        </form>
        <?php endif; ?>

        <?php if ($user && $qrToken): ?>
        <div class="space-y-4">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-xs text-emerald-900 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                <span>QR code valid. Welcome, <strong><?= htmlspecialchars($user['name']) ?></strong>.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <button onclick="qrCheckIn('CLOCK_IN')" class="py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition-all shadow-md">
                    Clock In
                </button>
                <button onclick="qrCheckIn('BREAK_START')" class="py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-sm transition-all shadow-md">
                    Start Break
                </button>
                <button onclick="qrCheckIn('CLOCK_OUT')" class="py-3 bg-zinc-800 hover:bg-zinc-900 text-white font-bold rounded-xl text-sm transition-all shadow-md">
                    Clock Out
                </button>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($user && !$qrToken): ?>
        <div class="space-y-4">
            <div class="bg-zinc-50 border-2 border-dashed border-zinc-300 rounded-xl p-6 text-center">
                <p class="text-xs text-zinc-500 mb-2">Scan the QR code displayed at the reception terminal</p>
                <div id="qr-scanner" class="w-full overflow-hidden rounded-xl bg-black"></div>
                <p id="scan-status" class="text-xs text-zinc-400 mt-2">Initializing camera...</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const qrToken = <?= json_encode($qrToken) ?>;

    <?php if ($user && !$qrToken): ?>
    let html5QrcodeScanner = null;

    function startScanner() {
        if (!window.Html5Qrcode) {
            document.getElementById('scan-status').innerText = 'Scanner library not loaded. Please refresh.';
            return;
        }
        html5QrcodeScanner = new Html5Qrcode('qr-scanner');
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                const url = new URL(decodedText);
                const token = url.searchParams.get('token');
                if (token) {
                    window.location.href = '/checkin/qr?token=' + encodeURIComponent(token);
                } else {
                    alert('Invalid QR code. Please scan the INNOW terminal QR code.');
                }
            },
            () => {}
        ).catch(err => {
            document.getElementById('scan-status').innerText = 'Camera access denied or unavailable. You can manually enter the token.';
        });
    }

    document.addEventListener('DOMContentLoaded', startScanner);
    <?php endif; ?>

    <?php if ($user && $qrToken): ?>
    async function qrCheckIn(action) {
        try {
            const res = await authFetch('/api/checkin/button', {
                method: 'POST',
                body: JSON.stringify({ qr_token: qrToken, action: action })
            });
            const data = await res.json();
            if (data.success) {
                alert('Success: ' + data.message);
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            alert('Network error. Please try again.');
        }
    }
    <?php endif; ?>

    async function handleQRLogin(e) {
        e.preventDefault();
        const email = document.getElementById('qr-email').value;
        const pin = document.getElementById('qr-pin').value;

        try {
            const res = await fetch('/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, pin })
            });
            const data = await res.json();
            if (data.success) {
                const url = new URL(window.location.href);
                const token = url.searchParams.get('token');
                if (token) {
                    window.location.href = '/checkin/qr?token=' + encodeURIComponent(token);
                } else {
                    window.location.href = '/dashboard';
                }
            } else {
                alert(data.message || 'Login failed.');
            }
        } catch (err) {
            alert('Connection error. Please try again.');
        }
    }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
